<?php

namespace App\Livewire\Accessory;

use App\Enums\EntityStatus;
use App\Models\AccessoryType;
use App\Models\DistributionCenter;
use App\Models\User;
use App\Services\DistributionCenter\DistributionCenterService;
use HarroldWafo\LaravelCustomDatatable\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filters\NumberFilter;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;

class AccessoryDataTable extends BaseDataTable
{
    protected $model = AccessoryType::class;

    public $selectedDistributionCenterId = null;

    public function columns(): array
    {
        return [
            Column::make('ID', 'id')
                ->sortable()
                ->searchable(),
            Column::make('Nom', 'name')
                ->sortable()
                ->searchable(),
            Column::make('Prix', 'price')
                ->format(
                    fn ($value, $row) => number_format($value, 0, ',', ' ').' FCFA'
                )
                ->sortable(),
            Column::make("Date d'enregistrement", 'created_at')
                ->format(
                    fn ($value, $row) => $value->format('d/m/Y')
                )
                ->sortable(),
            Column::make('État', 'is_active')
                ->format(
                    fn ($value, $row) => $value ?
                        '<span class="badge text-bg-'.EntityStatus::ACTIVE()->badge().'">'.EntityStatus::ACTIVE()->label.'</span>' :
                        '<span class="badge text-bg-'.EntityStatus::INACTIVE()->badge().'">'.EntityStatus::INACTIVE()->label.'</span>'
                )
                ->html()
                ->sortable(),
            Column::make('Stock', 'id')
                ->format(
                    function ($value, $row) {
                        $distCenterId = $this->getAppliedFilterValue('centre_de_distribution');

                        if ($distCenterId) {
                            $centerIds = [$distCenterId];
                        } else {
                            /** @var User $user */
                            $user = auth()->user();
                            $centerIds = $user->accessibleDistributionCenters()->pluck('distribution_centers.id')->toArray();
                        }
                        $totalQuantity = $row->getStockForType($centerIds);

                        return $totalQuantity > 0 ?
                            '<span class="badge text-bg-success">'.$totalQuantity.'</span>' :
                            '<span class="badge text-bg-danger">0</span>';
                    }
                )
                ->html()
                ->sortable(),

            Column::make('Actions', 'id')
                ->format(
                    function ($value, $row) {
                        return view('accessories.actions', ['product' => $row]);
                    }
                ),
        ];
    }

    public function filters(): array
    {
        /** @var User $user */
        $user = auth()->user();
        $distCenters = $user->accessibleDistributionCenters()->orderBy('name')->get();

        $distCenterOptions = ['' => 'Tous'];
        /** @var DistributionCenter $center */
        foreach ($distCenters as $center) {
            $distCenterOptions[$center->id] = $center->name;
        }

        return [
            SelectFilter::make('Centre de distribution')
                ->options($distCenterOptions)
                ->filter(function (Builder $builder, string $value) {
                    if ($value === '') {
                        $this->selectedDistributionCenterId = null;

                        return $builder;
                    }

                    $this->selectedDistributionCenterId = (int) $value;

                    return $builder;
                }),

            SelectFilter::make('État')
                ->options(array_merge(['' => 'Tous'], EntityStatus::labels()))
                ->filter(function (Builder $builder, string $value) {
                    if ($value === '') {
                        return $builder;
                    }

                    $isActive = $value === EntityStatus::ACTIVE()->value;

                    return $builder->where('is_active', $isActive);
                }),

            NumberFilter::make('Prix Min (FCFA)')
                ->config([
                    'placeholder' => 'Prix minimum',
                ])
                ->filter(function (Builder $builder, string $value) {
                    return $builder->where('price', '>=', $value);
                }),

            NumberFilter::make('Prix Max (FCFA)')
                ->config([
                    'placeholder' => 'Prix maximum',
                ])
                ->filter(function (Builder $builder, string $value) {
                    return $builder->where('price', '<=', $value);
                }),

            NumberFilter::make('Stock Min')
                ->config([
                    'placeholder' => 'Stock minimum',
                ])
                ->filter(function (Builder $builder, string $value) {
                    $distCenterId = $this->getAppliedFilterValue('centre_de_distribution');

                    if ($distCenterId) {
                        return $builder->whereHas('productCategories.distributionCenters', function ($query) use ($value, $distCenterId) {
                            $query->where('distribution_center_id', $distCenterId)
                                ->where('stock', '>=', $value);
                        });
                    } else {
                        $centerIds = DistributionCenterService::getForCurrentUser()->pluck('id')->toArray();

                        // Récupérer les IDs des AccessoryType qui ont un stock >= value
                        $accessoryTypeIds = AccessoryType::query()
                            ->join('product_categories as pc', 'accessory_types.id', '=', 'pc.product_type_id')
                            ->join('product_category_distribution_center as pcdc', 'pc.id', '=', 'pcdc.product_category_id')
                            ->where('pc.product_type', 'accessory')
                            ->whereIn('pcdc.distribution_center_id', $centerIds)
                            ->whereNull('pc.deleted_at')
                            ->groupBy('accessory_types.id')
                            ->havingRaw('SUM(pcdc.stock) >= ?', [$value])
                            ->pluck('accessory_types.id');

                        return $builder->whereIn('id', $accessoryTypeIds);
                    }
                }),

            NumberFilter::make('Stock Max')
                ->config([
                    'placeholder' => 'Stock maximum',
                ])
                ->filter(function (Builder $builder, string $value) {
                    $distCenterId = $this->getAppliedFilterValue('centre_de_distribution');

                    if ($distCenterId) {
                        return $builder->whereHas('productCategories.distributionCenters', function ($query) use ($value, $distCenterId) {
                            $query->where('distribution_center_id', $distCenterId)
                                ->where('stock', '<=', $value);
                        });
                    } else {
                        $centerIds = DistributionCenterService::getForCurrentUser()->pluck('id')->toArray();

                        $accessoryTypeIds = AccessoryType::query()
                            ->join('product_categories as pc', 'accessory_types.id', '=', 'pc.product_type_id')
                            ->join('product_category_distribution_center as pcdc', 'pc.id', '=', 'pcdc.product_category_id')
                            ->where('pc.product_type', 'accessory')
                            ->whereIn('pcdc.distribution_center_id', $centerIds)
                            ->whereNull('pc.deleted_at')
                            ->groupBy('accessory_types.id')
                            ->havingRaw('SUM(pcdc.stock) <= ?', [$value])
                            ->pluck('accessory_types.id');

                        return $builder->whereIn('id', $accessoryTypeIds);
                    }
                }),

        ];
    }

    public function builder(): Builder
    {
        $distCenterId = $this->getAppliedFilterValue('centre_de_distribution');

        if ($distCenterId) {
            $centerIds = [$distCenterId];
        } else {
            $distributionCenters = DistributionCenterService::getForCurrentUser();
            $centerIds = $distributionCenters->pluck('id')->toArray();
        }

        return AccessoryType::query()
            ->with(['productCategories.distributionCenters' => function ($query) use ($centerIds) {
                $query->whereIn('distribution_center_id', $centerIds);
            }]);
    }

    /**
     * Helper method to get applied filter value
     */
    protected function getAppliedFilterValue(string $filterKey): ?string
    {
        if (isset($this->appliedFilters[$filterKey])) {
            $value = $this->appliedFilters[$filterKey];

            return $value;
        }

        return null;
    }

    /**
     * Toggle the active status of an accessory type
     */
    public function toggleAccessoryStatus($accessoryTypeId)
    {
        try {
            $accessoryType = AccessoryType::find($accessoryTypeId);

            if (! $accessoryType) {
                $this->dispatch('show-notification', [
                    'type' => 'error',
                    'title' => 'Erreur !',
                    'message' => "L'accessoire sélectionné n'existe pas.",
                    'timer' => 3000,
                ]);

                return;
            }

            $currentStatus = $accessoryType->is_active;
            $newStatus = ! $currentStatus;

            $accessoryType->is_active = $newStatus;
            $result = $accessoryType->save();

            if ($result) {
                $status = $newStatus ? 'activé' : 'désactivé';
                $name = $accessoryType->name;

                session()->flash('success', "L'accessoire a été {$status} avec succès.");

                return redirect()->route('accessories.index')->with('success', "L'accessoire {$name} a été {$status} avec succès.");
            }
        } catch (\Exception $e) {
            Log::error('Error toggling accessory status: '.$e->getMessage());

            $this->dispatch('show-notification', [
                'type' => 'error',
                'title' => 'Erreur !',
                'message' => "Une erreur s'est produite lors de la modification du statut de l'accessoire.",
                'timer' => 3000,
            ]);
        }
    }

    /**
     * Delete an accessory type
     */
    public function deleteAccessory($accessoryTypeId)
    {
        try {
            $accessoryType = AccessoryType::find($accessoryTypeId);

            if (! $accessoryType) {
                $this->dispatch('show-notification', [
                    'type' => 'error',
                    'title' => 'Erreur !',
                    'message' => "L'accessoire sélectionné n'existe pas.",
                    'timer' => 3000,
                ]);

                return;
            }

            $name = $accessoryType->name;
            $result = $accessoryType->delete();

            if ($result) {
                session()->flash('success', "L'accessoire a été supprimé avec succès.");

                return redirect()->route('accessories.index')->with('success', "L'accessoire {$name} a été supprimé avec succès.");
            }
        } catch (\Exception $e) {
            Log::error('Error deleting accessory: '.$e->getMessage());

            $this->dispatch('show-notification', [
                'type' => 'error',
                'title' => 'Erreur !',
                'message' => "Une erreur s'est produite lors de la suppression de l'accessoire.",
                'timer' => 3000,
            ]);
        }
    }
}
