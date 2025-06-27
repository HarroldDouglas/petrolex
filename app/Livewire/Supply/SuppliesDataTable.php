<?php

namespace App\Livewire\Supply;

use App\Enums\SupplierDeliveryStatus;
use App\Models\DistributionCenter;
use App\Models\SupplierDelivery;
use App\Models\User;
use HarroldWafo\LaravelCustomDatatable\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
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

            Column::make('Centre de distr.', 'distribution_center_id')
                ->sortable()
                ->searchable(function (Builder $query, string $searchTerm) {
                    return $query->whereHas('distributionCenter', function (Builder $q) use ($searchTerm) {
                        $q->where('name', 'like', '%'.$searchTerm.'%');
                    });
                })
                ->format(function ($value, $row) {
                    return $row->distributionCenter->name ?? '-';
                }),

            Column::make('Produits', 'id')
                ->format(function ($value, $row) {
                    $productTypes = $row->productTypes;
                    if ($productTypes->isEmpty()) {
                        return '-';
                    }

                    $html = '';
                    foreach ($productTypes as $productType) {
                        $name = $productType->productCategory->name ?? '';

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
                    return new HtmlString(view('components.supply-actions', ['supply' => $row])->render());
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
                            $query->where('product_type', 'bottle');
                        });
                    }

                    if ($value === 'only_bottles') {
                        // supplies with only bottles and no other product types
                        return $builder->whereDoesntHave('productTypes', function (Builder $query) {
                            $query->where('product_type', '!=', 'bottle');
                        })->whereHas('productTypes', function (Builder $query) {
                            $query->where('product_type', 'bottle');
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

    public function cancelSupply(int $supplyId): void
    {
        try {
            $supply = SupplierDelivery::findOrFail($supplyId);
            $supply->status = SupplierDeliveryStatus::CANCELLED();
            $supply->save();

            $this->dispatch('show-notification', [
                'type' => 'success',
                'title' => 'Annulée !',
                'message' => 'Approvisionnement annulé avec succès.',
                'timer' => 3000,
            ]);

        }  catch (\Exception $e) {
            Log::error("Error cancelling supply #{$supplyId}: " . $e->getMessage(), ['exception' => $e]);

            $this->dispatch('show-notification', [
                'type' => 'error',
                'title' => 'Erreur !',
                'message' => 'Une erreur est survenue lors de l\'annulation de l\'approvisionement.',
                'timer' => 3000,
            ]);
        }
    }

}
