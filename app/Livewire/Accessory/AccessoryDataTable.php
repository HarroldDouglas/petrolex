<?php

namespace App\Livewire\Accessory;

use App\Enums\EntityStatus;
use App\Models\AccessoryType;
use App\Models\DistributionCenter;
use App\Models\User;
use HarroldWafo\LaravelCustomDatatable\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder;
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
                        return $builder->whereHas('accessories', function ($query) use ($value, $distCenterId) {
                            $query->select('accessory_type_id')
                                ->where('distribution_center_id', $distCenterId)
                                ->groupBy('accessory_type_id')
                                ->havingRaw('SUM(quantity) >= ?', [$value]);
                        });
                    } else {
                        return $builder->having('accessories_sum_quantity', '>=', $value);
                    }
                }),

            NumberFilter::make('Stock Max')
                ->config([
                    'placeholder' => 'Stock maximum',
                ])
                ->filter(function (Builder $builder, string $value) {
                    $distCenterId = $this->getAppliedFilterValue('centre_de_distribution');

                    if ($distCenterId) {
                        return $builder->whereHas('accessories', function ($query) use ($value, $distCenterId) {
                            $query->select('accessory_type_id')
                                ->where('distribution_center_id', $distCenterId)
                                ->groupBy('accessory_type_id')
                                ->havingRaw('SUM(quantity) <= ?', [$value]);
                        });
                    } else {
                        return $builder->having('accessories_sum_quantity', '<=', $value);
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
            /** @var User $user */
            $user = auth()->user();
            $centerIds = $user->accessibleDistributionCenters()->pluck('distribution_centers.id')->toArray();
        }

        return AccessoryType::query()
            ->with(['accessories' => function ($query) use ($centerIds) {
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
}
