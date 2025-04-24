<?php

namespace App\Livewire;

use App\Exports\UsersExport;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filters\DateFilter;
use Rappasoft\LaravelLivewireTables\Views\Filters\DateRangeFilter;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;
use Rappasoft\LaravelLivewireTables\Views\Filters\TextFilter;

class UsersTable extends DataTableComponent
{
    protected $model = User::class;

    // IMPORTANT : Utilisez des propriétés protégées (non public) pour ces options
    protected $showExportOption = true;
    protected $exports = ['csv', 'xlsx', 'pdf']; // Définir les formats disponibles directement
    protected $exportFileName = 'utilisateurs-export'; // Nom du fichier d'exportation

    public bool $rememberColumnSelection = true;
    public bool $rememberFilters = true;
    public bool $rememberSort = true;
    public bool $rememberPerPage = true;

    public function configure(): void
    {
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
            ->setDefaultSort('last_name', 'asc')
            ->setPerPageAccepted([10, 25, 50, 100])
            ->setPerPage(10)
            ->setBulkActions([
                'exportExcel' => 'Exporter en Excel',
                'exportCsv' => 'Exporter en CSV',
            ]);
    }

    public function columns(): array
    {
        return [
            Column::make('ID', 'id')
                ->sortable()
                ->deselected(),

            Column::make('Nom', 'last_name')
                ->sortable()
                ->searchable(),

            Column::make('Prénom', 'first_name')
                ->sortable()
                ->searchable(),

            Column::make('Email', 'email')
                ->sortable()
                ->searchable(),

            Column::make('Téléphone', 'phone_number')
                ->sortable()
                ->searchable(),

            Column::make('Actif', 'is_active')
                ->sortable()
                ->format(fn ($value) => $value ? 'Oui' : 'Non'),

            Column::make('Dernière connexion', 'last_login_at')
                ->sortable()
                ->format(function ($value) {
                    return $value ? $value->format('d/m/Y H:i') : '-';
                }),

            Column::make('Créé le', 'created_at')
                ->sortable()
                ->format(fn ($value) => $value->format('d/m/Y'))
                ->deselected(),

            Column::make('Actions')
                ->label(
                    function ($row) {
                        return view('components.user-actions', ['user' => $row]);
                    }
                ), // Exclure cette colonne des exports
        ];
    }

    public function filters(): array
    {
        return [
            TextFilter::make('Recherche par nom')
                ->config(['placeholder' => 'Rechercher un nom...'])
                ->filter(function (Builder $builder, string $value) {
                    $builder->where(function ($query) use ($value) {
                        $query->where('last_name', 'like', '%'.$value.'%')
                            ->orWhere('first_name', 'like', '%'.$value.'%');
                    });
                }),

            SelectFilter::make('Statut', 'is_active')
                ->options([
                    '' => 'Tous',
                    '1' => 'Actif',
                    '0' => 'Inactif',
                ])
                ->filter(function (Builder $builder, string $value) {
                    if ($value === '') {
                        return $builder;
                    }

                    return $builder->where('is_active', $value);
                }),

            DateFilter::make('Créé après', 'created_at')
                ->config([
                    'placeholder' => 'Date de création minimum',
                    'locale' => 'fr',
                ])
                ->filter(function (Builder $builder, string $value) {
                    $builder->whereDate('created_at', '>=', $value);
                }),

            DateRangeFilter::make('Dernière connexion')
                ->config([
                    'locale' => 'fr',
                    'altFormat' => 'd/m/Y',
                ])
                ->filter(function (Builder $builder, array $dateRange) {
                    $builder->whereBetween('last_login_at', [$dateRange['minDate'].' 00:00:00', $dateRange['maxDate'].' 23:59:59']);
                }),
        ];
    }

    public function exportExcel()
    {
        $users = $this->getSelected();
        $this->clearSelected();

        return Excel::download(new UsersExport($users), 'utilisateurs-'.date('Y-m-d').'.xlsx');
    }

    public function exportCsv()
    {
        $users = $this->getSelected();
        $this->clearSelected();

        return Excel::download(new UsersExport($users), 'utilisateurs-'.date('Y-m-d').'.csv');
    }
}
