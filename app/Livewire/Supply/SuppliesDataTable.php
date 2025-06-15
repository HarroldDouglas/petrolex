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
use Rappasoft\LaravelLivewireTables\Views\Filters\DateFilter;
use Rappasoft\LaravelLivewireTables\Views\Filters\DateRangeFilter;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;
use Rappasoft\LaravelLivewireTables\Views\Filters\TextFilter;

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
                    return new HtmlString('<a href="'.route('supplies.details', $row->id).'">'.$value.'</a>');
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
                        $name = '';
                        if ($productType->product_type->isBottle() && $productType->bottleType) {
                            $name = $productType->bottleType->name;
                        } elseif ($productType->product_type->isAccessory() && $productType->accessoryType) {
                            $name = $productType->accessoryType->name;
                        }

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
                    $html = '<div class="btn-group dropdown-icon-none">
                                <button class="btn btn-light-primary icon-btn w-30 h-30 me-0 dropdown-toggle"
                                    type="button" id="dropdownMenuButton'.$row->id.'" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <i class="ti ti-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton'.$row->id.'">
                                    <li>
                                        <a class="dropdown-item" href="'.route('supplies.details', $row->id).'">
                                            <i class="ti ti-eye text-primary me-2"></i> Détail
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="'.route('supplies.edit', $row->id).'">
                                            <i class="ti ti-edit text-success me-2"></i> Editer
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item delete-btn" href="#" 
                                           data-id="'.$row->id.'" 
                                           data-reference="'.$row->delivery_number.'">
                                            <i class="ti ti-trash text-danger me-2"></i> Supprimer
                                        </a>
                                    </li>
                                </ul>
                            </div>';

                    return new HtmlString($html);
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

            TextFilter::make('Reference')
                ->config(['placeholder' => 'Rechercher une référence...'])
                ->filter(function (Builder $builder, string $value) {
                    $builder->where('delivery_number', 'like', '%'.$value.'%');
                }),

            SelectFilter::make('Statut')
                ->options(array_merge(['' => 'Tous'] + SupplierDeliveryStatus::labels()))
                ->filter(function (Builder $builder, string $value) {
                    if ($value === '') {
                        return $builder;
                    }

                    return $builder->where('status', $value);
                }),

            DateFilter::make('Date après')
                ->config([
                    'placeholder' => 'Date minimum',
                    'locale' => 'fr',
                ])
                ->filter(function (Builder $builder, string $value) {
                    $builder->whereDate('supply_date', '>=', $value);
                }),

            DateRangeFilter::make('Période')
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
                'productTypes.bottleType',
                'productTypes.accessoryType',
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
}
