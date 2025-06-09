<?php

namespace App\Livewire\Bottle;

use App\Models\BottleType;
use App\Services\Bottle\BottleTypeService;
use HarroldWafo\LaravelCustomDatatable\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;
use Rappasoft\LaravelLivewireTables\Views\Filters\DateFilter;
use Rappasoft\LaravelLivewireTables\Views\Filters\TextFilter;

class BottleTypeDataTable extends BaseDataTable
{
    protected $model = BottleType::class;

    protected const DEFAULT_SORT_FIELD = 'name';
    protected const DEFAULT_SORT_DIRECTION = 'asc';

    protected $listeners = ['refreshComponent' => '$refresh'];

    protected function getExportFileName(): string
    {
        return 'types_de_bouteilles';
    }

    public function columns(): array
    {
        return [
            Column::make('Nom', 'name')
                ->sortable()
                ->searchable(),

            Column::make('Capacité', 'capacity')
                ->sortable()
                ->searchable(),

            Column::make('Hauteur', 'height')
                ->sortable(),

            Column::make('Largeur', 'width')
                ->sortable(),

            Column::make('Rayon', 'radius')
                ->sortable(),

            Column::make('Prix du contenu', 'content_price')
                ->sortable(),

            Column::make('Prix bouteille + contenu', 'bottle_with_content_price')
                ->sortable(),

            Column::make('Actif', 'is_active')
                ->sortable()
                ->format(function ($value) {
                    $badgeClass = $value ? 'text-bg-success' : 'text-bg-danger';
                    $text = $value ? 'Oui' : 'Non';
                    return new HtmlString('<span class="badge '.$badgeClass.'">'.e($text).'</span>');
                }),

            Column::make('Date de création', 'created_at')
                ->sortable()
                ->format(fn ($value) => $value->format('d/m/Y H:i')),

            Column::make('Action', 'id')
                ->format(function ($value, $row) {
                    return new HtmlString(
                        view('components.bottle-type-actions', ['bottleType' => $row])->render()
                    );
                }),
        ];
    }

    public function filters(): array
    {
        return [
            TextFilter::make('Nom du type')
                ->config(['placeholder' => 'Rechercher par nom...'])
                ->filter(function (Builder $builder, string $value) {
                    $builder->where('name', 'like', '%'.$value.'%');
                }),

            SelectFilter::make('Actif')
                ->options([
                    '' => 'Tous',
                    '1' => 'Oui',
                    '0' => 'Non',
                ])
                ->filter(function (Builder $builder, string $value) {
                    if ($value !== '') {
                        $builder->where('is_active', (bool) $value);
                    }
                }),

            // You might add a date range filter if useful
            DateFilter::make('Créé après')
                ->config(['placeholder' => 'Date minimum', 'locale' => 'fr'])
                ->filter(function (Builder $builder, string $value) {
                    $builder->whereDate('created_at', '>=', $value);
                }),
        ];
    }

    public function builder(): Builder
    {
        return BottleType::query(); 
    }

    protected function customMapAttributes()
    {
        return [
            'is_active_formatted' => function ($row) {
                return $row->is_active ? 'Oui' : 'Non';
            },
            'created_at_formatted' => function ($row) {
                return $row->created_at ? $row->created_at->format('d/m/Y H:i') : '-';
            },
            
        ];
    }
    
    /**
     * Dispatches an event to show the edit modal for a specific bottle type.
     */
    public function showEditBottleTypeModal(int $bottleTypeId)
    {
        $this->dispatch('showEditBottleTypeModal', $bottleTypeId);
    }

    /**
     * Handles the deletion of a bottle type.
     */
    public function deleteBottleType(int $bottleTypeId)
    {
        try {
            app(\App\Services\Bottle\BottleTypeService::class)->deleteBottleType($bottleTypeId);

            session()->flash('success', 'Type de bouteille supprimé avec succès!');
            $this->dispatch('refreshComponent');
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur lors de la suppression du type de bouteille.');
        }
    }

    /**
     * Toggles the active status of a bottle type.
     */
    public function toggleBottleTypeStatus(int $bottleTypeId, bool $isActive)
    {
        try {
            $bottleTypeService = app(\App\Services\Bottle\BottleTypeService::class);
            $bottleTypeService->updateActiveStatus($bottleTypeId, $isActive);

            session()->flash('success', 'Statut du type de bouteille modifié avec succès!');
            $this->dispatch('refreshComponent');
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur lors de la modification du statut.');
        }
    }

}