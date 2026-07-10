<?php

namespace App\Livewire\Supply;

use App\Enums\SupplierDeliveryStatus;
use App\Models\DistributionCenter;
use App\Models\SupplierDelivery;
use App\Models\User;
use HarroldWafo\LaravelCustomDatatable\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filters\DateRangeFilter;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;

class SuppliesDataTable extends BaseDataTable
{
    protected $model = SupplierDelivery::class;

    protected const DEFAULT_SORT_FIELD = 'supply_date';
    protected const DEFAULT_SORT_DIRECTION = 'desc';

    protected function getExportFileName(): string
    {
        return 'approvisionnements';
    }

    public function columns(): array
    {
        return [
            Column::make('Reference', 'delivery_number')
                ->sortable()
                ->searchable()
                ->format(function ($value, $row) {
                    return new HtmlString('<a href="'.route('supplies.edit', $row->id).'">'.$value.'</a>');
                }),

            Column::make('Centre de distr.', 'distributionCenter.name')
                ->sortable()
                ->searchable(),

            Column::make('Produits', 'id')
                ->format(function ($value, $row) {
                    $productTypes = $row->productTypes;
                    if ($productTypes->isEmpty()) {
                        return '-';
                    }

                    $html = '';
                    foreach ($productTypes as $productType) {
                        $name = $productType->productCategory->name;

                        if ($name) {
                            $html .= '<span class="badge rounded-pill bg-light-secondary mb-1">'
                                .e($name).'</span> ';
                        }
                    }

                    return new HtmlString($html ?: '-');
                }),

            Column::make('Date', 'supply_date')
                ->sortable()
                ->format(fn ($value) => $value->format('d M,Y H:i')),

            Column::make('Statut', 'status')
                ->sortable()
                ->format(function ($value) {
                    return new HtmlString(
                        '<span class="badge '.$value->badge().'">'.e($value->label).'</span>'
                    );
                }),

            Column::make('Actions', 'id')
                ->format(function ($value, $row) {
                    return new HtmlString(view('partials.supplies.actions', ['supply' => $row])->render());
                }),
        ];
    }

    /**
     * Get authorized distribution center options for the current user
     */
    protected function getDistributionCenterOptions(): array
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            return ['' => 'Tous'];
        }

        $centerIds = $user->distributionCenters()->pluck('distribution_center_id')->toArray();
        $centers = DistributionCenter::whereIn('id', $centerIds)->orderBy('name')->get();

        $options = ['' => 'Tous'];

        foreach ($centers as $center) {
            $options[$center->id] = $center->name;
        }

        return $options;
    }

    public function filters(): array
    {
        return [
            SelectFilter::make('Centre de distribution')
                ->options($this->getDistributionCenterOptions())
                ->filter(function (Builder $builder, string $value) {
                    if ($value === '') {
                        return $builder;
                    }

                    return $builder->where('distribution_center_id', $value);
                }),

            SelectFilter::make('Type de livraison')
                ->options([
                    '' => 'Tous types',
                    'has_bottles' => 'Incluant des bouteilles',
                    'only_bottles' => 'Uniquement des bouteilles',
                ])
                ->filter(function (Builder $builder, string $value) {
                    if ($value === '') {
                        return $builder;
                    }

                    if ($value === 'has_bottles') {
                        // supplies with at least one bottle
                        return $builder->whereHas('productTypes', function (Builder $query) {
                            // @phpstan-ignore method.notFound
                            $query->bottles();
                        });
                    }

                    if ($value === 'only_bottles') {
                        // supplies with only bottles and no other product types
                        return $builder->whereDoesntHave('productTypes', function (Builder $query) {
                            // @phpstan-ignore method.notFound
                            $query->accessories();
                        })->whereHas('productTypes', function (Builder $query) {
                            // @phpstan-ignore method.notFound
                            $query->bottles();
                        });
                    }

                    return $builder;
                }),

            SelectFilter::make('Statut')
                ->options(array_merge(['' => 'Tous'] + SupplierDeliveryStatus::labels()))
                ->filter(function (Builder $builder, string $value) {
                    if ($value === '') {
                        return $builder;
                    }

                    return $builder->where('status', $value);
                }),

            DateRangeFilter::make('Période de date de livraison')
                ->config([
                    'locale' => 'fr',
                    'altFormat' => 'd/m/Y',
                ])
                ->filter(function (Builder $builder, array $dateRange) {
                    $builder->whereBetween('supply_date', [$dateRange['minDate'].' 00:00:00', $dateRange['maxDate'].' 23:59:59']);
                }),
        ];
    }

    public function builder(): Builder
    {
        $query = SupplierDelivery::query()
            ->with([
                'distributionCenter',
                'productTypes.productCategory',
                'user',
            ]);

        /** @var User|null $user */
        $user = Auth::user();

        $centerIds = $user->distributionCenters()->pluck('distribution_center_id')->toArray();
        if (! empty($centerIds)) {
            $query->whereIn('distribution_center_id', $centerIds);
        }

        return $query;
    }

    /**
     * Delete a supply directly.
     */
    public function deleteSupply($supplyId)
    {
        try {
            $supply = SupplierDelivery::find($supplyId);

            if (! $supply) {
                $this->dispatch('show-notification', [
                    'type' => 'error',
                    'title' => 'Erreur !',
                    'message' => "L'approvisionnement sélectionné n'existe pas.",
                    'timer' => 3000,
                ]);

                return;
            }

            $deliveryNumber = $supply->delivery_number;
            $bottleIds = $supply->productTypes()
                ->with('deliveryBottles:id,bottle_id,supplier_delivery_product_type_id')
                ->get()
                ->flatMap(fn ($pt) => $pt->deliveryBottles->pluck('bottle_id'));

            if ($supply->delete()) {
                app(\App\Services\Supply\BottleReleaseService::class)
                    ->releaseOrphanedBottles($bottleIds);

                $this->dispatch('show-notification', [
                    'type' => 'success',
                    'title' => 'Supprimé !',
                    'message' => "L'approvisionnement {$deliveryNumber} a été supprimé avec succès.",
                    'timer' => 3000,
                ]);
            } else {
                $this->dispatch('show-notification', [
                    'type' => 'error',
                    'title' => 'Erreur !',
                    'message' => "Échec de la suppression de l'approvisionnement.",
                    'timer' => 3000,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error deleting supply: '.$e->getMessage());
            $this->dispatch('show-notification', [
                'type' => 'error',
                'title' => 'Erreur !',
                'message' => "Une erreur s'est produite lors de la suppression de l'approvisionnement.",
                'timer' => 3000,
            ]);
        }
    }

    public function completeSupply(int $supplyId): void
    {
        try {
            $supply = SupplierDelivery::findOrFail($supplyId);

            // Vérifier que toutes les bouteilles sont scannées
            $allDone = true;
            foreach ($supply->productTypes as $productType) {
                if (! $productType->incoming_done || ($productType->bottles_out_quantity > 0 && ! $productType->outgoing_done)) {
                    $allDone = false;
                    break;
                }
            }

            if (! $allDone) {
                $this->dispatch('show-notification', [
                    'type' => 'warning',
                    'title' => 'Attention !',
                    'message' => 'Toutes les bouteilles n\'ont pas encore été scannées.',
                    'timer' => 3000,
                ]);

                return;
            }

            // Transitions at completion:
            // - incoming scanned bottles: PENDING_RECEPTION → IN_STOCK
            // - outgoing scanned bottles: physically left with the supplier →
            //   RETURNED_TO_SUPPLIER + empty, so stock counts stay correct and
            //   the bottle can be received again when the supplier brings it back.
            $productTypes = $supply->productTypes()->with('deliveryBottles')->get();

            $incomingIds = $productTypes->flatMap(
                fn ($pt) => $pt->deliveryBottles
                    ->filter(fn ($row) => (string) $row->movement_type === 'incoming')
                    ->pluck('bottle_id')
            );
            $outgoingIds = $productTypes->flatMap(
                fn ($pt) => $pt->deliveryBottles
                    ->filter(fn ($row) => (string) $row->movement_type === 'outgoing')
                    ->pluck('bottle_id')
            );

            if ($incomingIds->isNotEmpty()) {
                \App\Models\Bottle::whereIn('id', $incomingIds)
                    ->where('status', \App\Enums\BottleStatus::PENDING_RECEPTION())
                    ->update(['status' => \App\Enums\BottleStatus::IN_STOCK()]);
            }

            if ($outgoingIds->isNotEmpty()) {
                \App\Models\Bottle::whereIn('id', $outgoingIds)
                    ->update([
                        'status' => \App\Enums\BottleStatus::RETURNED_TO_SUPPLIER(),
                        'is_filled' => false,
                    ]);
            }

            $supply->status = SupplierDeliveryStatus::COMPLETED();
            $supply->save();

            // Synchroniser le stock après avoir terminé l'approvisionnement
            $stockService = app(\App\Services\Stock\StockSynchronizationService::class);
            $stockService->synchronizeAllBottleStockForCenter($supply->distribution_center_id);

            $this->dispatch('show-notification', [
                'type' => 'success',
                'title' => 'Terminé !',
                'message' => 'Approvisionnement marqué comme terminé avec succès.',
                'timer' => 3000,
            ]);
        } catch (\Exception $e) {
            Log::error('Error completing supply: '.$e->getMessage());
            $this->dispatch('show-notification', [
                'type' => 'error',
                'title' => 'Erreur !',
                'message' => 'Une erreur s\'est produite.',
                'timer' => 3000,
            ]);
        }
    }

    public function cancelSupply(int $supplyId): void
    {
        try {
            $supply = SupplierDelivery::findOrFail($supplyId);

            if (! $supply->canBeEdited()) {
                $this->dispatch('show-notification', [
                    'type' => 'error',
                    'title' => 'Erreur !',
                    'message' => 'Seul un approvisionnement en cours peut être annulé.',
                    'timer' => 3000,
                ]);

                return;
            }

            $supply->status = SupplierDeliveryStatus::CANCELLED();
            $supply->save();

            $bottleIds = $supply->productTypes()
                ->with('deliveryBottles:id,bottle_id,supplier_delivery_product_type_id')
                ->get()
                ->flatMap(fn ($pt) => $pt->deliveryBottles->pluck('bottle_id'));
            app(\App\Services\Supply\BottleReleaseService::class)
                ->releaseOrphanedBottles($bottleIds);

            $this->dispatch('show-notification', [
                'type' => 'success',
                'title' => 'Annulée !',
                'message' => 'Approvisionnement annulé avec succès.',
                'timer' => 3000,
            ]);

        } catch (\Exception $e) {
            Log::error("Error cancelling supply #{$supplyId}: ".$e->getMessage(), ['exception' => $e]);

            $this->dispatch('show-notification', [
                'type' => 'error',
                'title' => 'Erreur !',
                'message' => 'Une erreur est survenue lors de l\'annulation de l\'approvisionement.',
                'timer' => 3000,
            ]);
        }
    }
}
