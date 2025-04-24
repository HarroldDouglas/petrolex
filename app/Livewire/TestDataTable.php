<?php

namespace App\Livewire;

use App\Models\User;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

class TestDataTable extends DataTableComponent
{
    protected $model = User::class;

    public function configure(): void
    {
        $this->setPrimaryKey('id')
            ->setTableWrapperAttributes([
                'class' => 'table-responsive',
                'style' => 'overflow: visible !important;',
            ])
            ->setTableAttributes([
                'class' => 'table table-hover',
            ])
            ->setPerPage(10) // Réduire à 5 pour forcer la pagination même avec peu de données
            ->setPaginationStatus(true)
            ->setPaginationVisibilityStatus(true);

        $this->dispatch('test-datatable-booted', [
            'paginationEnabled' => true,
            'perPage' => 10,
        ]);

    }

    public function columns(): array
    {
        return [
            Column::make('ID', 'id')
                ->sortable(),

            Column::make('Nom', 'last_name')
                ->sortable()
                ->searchable(),

            Column::make('Prénom', 'first_name')
                ->sortable()
                ->searchable(),

            Column::make('Email', 'email')
                ->sortable(),
        ];
    }

    // Méthode pour débugger - affiche le nombre d'enregistrements dans la console
    public function renderingTable()
    {
        $count = $this->getBuilder()->count();
        info('Total records in TestDataTable: '.$count);
    }
}
