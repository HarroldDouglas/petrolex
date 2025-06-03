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
        return 'bouteilles';
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
                        view('components.bottle-actions', ['bottle' => $row])->render()
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