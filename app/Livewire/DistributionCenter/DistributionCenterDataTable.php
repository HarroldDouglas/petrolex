<?php

namespace App\Livewire\DistributionCenter;

use App\Models\DistributionCenter;
use HarroldWafo\LaravelCustomDatatable\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Rappasoft\LaravelLivewireTables\Views\Column;

// TODO: we should add column rating(note moyenne), which can be calculated from the orders table
class DistributionCenterDataTable extends BaseDataTable
{
    protected $model = DistributionCenter::class;

    protected const DEFAULT_SORT_FIELD = 'created_at';
    protected const DEFAULT_SORT_DIRECTION = 'desc';

    protected function getExportFileName(): string
    {
        return 'centres-distribution';
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
            Column::make('Nom', 'name')
                ->sortable()
                ->searchable(),

            Column::make('Pays', 'country')
                ->sortable()
                ->searchable(),

            Column::make('Ville', 'city')
                ->sortable()
                ->searchable(),

            Column::make('Quartier', 'neighborhood')
                ->sortable()
                ->searchable(),

            Column::make('Adresse', 'address')
                ->sortable()
                ->searchable(),

            Column::make('Téléphone', 'phone')
                ->sortable()
                ->searchable(),

            Column::make('Date', 'created_at')
                ->sortable()
                ->format(fn ($value) => $value->format('d/m/Y')),

            Column::make('Statut', 'is_active')
                ->sortable()
                ->format(function ($value) {
                    $status = $value ? 'Actif' : 'Inactif';
                    $badgeClass = $value ? 'bg-success' : 'bg-danger';

                    return new HtmlString(
                        '<span class="badge '.$badgeClass.'">'.e($status).'</span>'
                    );
                }),

            // TODO: create an action vue for this and restore de dropdown action menu
            Column::make('Actions', 'id')
                ->format(function ($value, $row) {
                    return new HtmlString(
                        '<a href="'.route('distribution-centers.edit', $row->id).'" class="btn btn-sm btn-primary me-1"><i class="bi bi-pencil"></i> Modifier</a>'.
                        '<a href="'.route('distribution-centers.details', $row->id).'" class="btn btn-sm btn-info"><i class="bi bi-eye"></i> Détails</a>'
                    );
                }),
        ];
    }

    public function builder(): Builder
    {
        return DistributionCenter::query();
    }

    protected function customMapAttributes()
    {
        return [
            'status' => function ($row) {
                return $row->is_active ? 'Actif' : 'Inactif';
            },
        ];
    }
}
