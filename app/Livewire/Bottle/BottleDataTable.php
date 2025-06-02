<?php

namespace App\Livewire\Bottle;

use App\Enums\BottleStatus;
use App\Enums\ProductType;
use App\Models\Bottle;
use HarroldWafo\LaravelCustomDatatable\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filters\DateFilter;
use Rappasoft\LaravelLivewireTables\Views\Filters\DateRangeFilter;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;
use Rappasoft\LaravelLivewireTables\Views\Filters\TextFilter;

class BottleDataTable extends BaseDataTable
{
    protected $model = Bottle::class;

    protected const DEFAULT_SORT_FIELD = 'created_at';
    protected const DEFAULT_SORT_DIRECTION = 'desc';

    // Add listener for component refresh
    protected $listeners = ['refreshComponent' => '$refresh'];

    protected function getExportFileName(): string
    {
        return 'commandes';
    }

    public bool $rememberColumnSelection = true;
    public bool $rememberFilters = true;
    public bool $rememberSort = true;
    public bool $rememberPerPage = true;

    public function configure(): void
    {
        parent::configure();

        $this->setPrimaryKey('id')
            ->setTableWrapperAttributes([
                'class' => 'table-responsive',
            ])
            ->setTableAttributes([
                'class' => 'table table-striped table-hover',
            ])
            ->setTheadAttributes([
                'class' => 'table-light',
            ])
            ->setDefaultSort(self::DEFAULT_SORT_FIELD, self::DEFAULT_SORT_DIRECTION)
            ->setPerPageAccepted([10, 25, 50, 100])
            ->setPerPage(10);
    }

    public function columns(): array
    {
        return [
            Column::make('Code-barre', 'barcode')
                ->sortable()
                ->searchable(),

            Column::make('Type de bouteille', 'bottle_type_id')
                ->sortable()
                ->searchable(function (Builder $query, string $searchTerm) {
                    return $query->whereHas('bottleType', function (Builder $q) use ($searchTerm) {
                        $q->where('name', 'like', '%'.$searchTerm.'%');
                    });
                })
                ->format(function ($value, $row) {
                    return optional($row->bottleType)->name ?? '-';
                }),

            Column::make('Date d\'enregistrement', 'created_at')
                ->sortable()
                ->format(fn ($value) => $value->format('d/m/Y')),

            Column::make('État', 'status')
                ->sortable()
                ->format(function ($value) {
                    $badgeClass = match ($value) {
                        BottleStatus::IN_STOCK() => 'text-bg-primary',
                        BottleStatus::WITH_DELIVERY_PERSON() => 'text-bg-warning',
                        BottleStatus::WITH_CLIENT() => 'text-bg-info',
                        BottleStatus::LOST_STOLEN() => 'text-bg-danger',
                        BottleStatus::RETURNED_TO_SUPPLIER() => 'text-bg-dark',
                        default => 'text-bg-secondary',
                    };

                    return new HtmlString(
                        '<span class="badge '.$badgeClass.'">'.e($value->label).'</span>'
                    );
                }),

            Column::make('Action', 'id')
                ->format(function ($value, $row) {
                    return new HtmlString(
                        '<div class="btn-group dropdown-icon-none">
                            <button
                                class="btn btn-light-primary icon-btn w-30 h-30 me-0 dropdown-toggle"
                                type="button" id="dropdownMenuButton'.$row->id.'"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ti ti-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu"
                                aria-labelledby="dropdownMenuButton'.$row->id.'">
                                <li>
                                    <a class="dropdown-item view-history" href="#"
                                        data-id="'.$row->id.'" 
                                        data-barcode="'.$row->barcode.'"
                                        data-type="'.optional($row->bottleType)->name.'"
                                        data-bs-toggle="modal"
                                        data-bs-target="#historyModal">
                                        <i class="iconoir-clock-rotate-right text-primary me-2"></i>
                                        Historique
                                    </a>
                                </li>'
                                .($row->status->value !== BottleStatus::LOST_STOLEN()->value
                                    ? '<li>
                                            <a class="dropdown-item mark-lost" href="#"
                                                data-id="'.$row->id.'">
                                                <i class="iconoir-chat-bubble-question text-danger me-2"></i>
                                                Déclarer perdu
                                            </a>
                                        </li>'
                                    : '<li>
                                            <a class="dropdown-item mark-found" href="#"
                                                data-id="'.$row->id.'">
                                                <i class="iconoir-circle-spark text-success me-2"></i>
                                                Marquer retrouvée
                                            </a>
                                        </li>'
                                ).'
                            </ul>
                        </div>'
                    );
                }),
        ];
    }

    public function filters(): array
    {
        return [
            TextFilter::make('Type de bouteille')
                ->config(['placeholder' => 'Rechercher un type...'])
                ->filter(function (Builder $builder, string $value) {
                    $builder->whereHas('bottleType', function (Builder $q) use ($value) {
                        $q->where('name', 'like', '%'.$value.'%');
                    });
                }),

            SelectFilter::make('Etat')
                ->options(['' => 'Tous'] + BottleStatus::labels())
                ->filter(function (Builder $builder, string $value) {
                    if ($value === '') {
                        return;
                    }
                    $builder->where('status', $value);
                }),

            DateFilter::make('Date après')
                ->config([
                    'placeholder' => 'Date minimum',
                    'locale' => 'fr',
                ])
                ->filter(function (Builder $builder, string $value) {
                    $builder->whereDate('created_at', '>=', $value);
                }),

            DateRangeFilter::make('Période')
                ->config([
                    'locale' => 'fr',
                    'altFormat' => 'd/m/Y',
                ])
                ->filter(function (Builder $builder, array $dateRange) {
                    $builder->whereBetween('created_at', [$dateRange['minDate'].' 00:00:00', $dateRange['maxDate'].' 23:59:59']);
                }),
        ];
    }

    public function builder(): Builder
    {
        return Bottle::query()
            ->with([
            'bottleType',
            ]);
    }

    protected function customMapAttributes()
    {
        return [
            'bottle_type_name' => function ($row) {
            return optional($row->bottleType)->name ?? '-';
            },
            'status_formatted' => function ($row) {
            return $row->status->label ?? '-';
            },
            'created_at_formatted' => function ($row) {
            return $row->created_at ? $row->created_at->format('d/m/Y') : '-';
            },
            'barcode' => function ($row) {
            return $row->barcode ?? '-';
            },
        ];
    }
}