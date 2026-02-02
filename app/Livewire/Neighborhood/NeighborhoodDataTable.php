<?php

namespace App\Livewire\Neighborhood;

use App\Models\Geography\City;
use App\Models\Geography\Municipality;
use App\Models\Geography\Neighborhood;
use App\Services\Geography\NeighborhoodService;
use HarroldWafo\LaravelCustomDatatable\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;

class NeighborhoodDataTable extends BaseDataTable
{
    protected $model = Neighborhood::class;

    protected const DEFAULT_SORT_FIELD = 'name';
    protected const DEFAULT_SORT_DIRECTION = 'asc';

    protected function getExportFileName(): string
    {
        return 'quartiers';
    }

    public function columns(): array
    {
        return [
            Column::make('ID', 'id')
                ->sortable()
                ->deselected(),

            Column::make('Nom', 'name')
                ->sortable()
                ->searchable(),

            Column::make('Municipalité', 'municipality_id')
                ->sortable()
                ->searchable()
                ->format(fn ($value, $row) => $row->municipality->name ?? '-'),

            Column::make('Ville', 'municipality.city_id')
                ->sortable()
                ->format(fn ($value, $row) => $row->municipality->city->name ?? '-'),

            Column::make('Statut', 'is_active')
                ->sortable()
                ->format(function ($value) {
                    $badgeClass = $value ? 'text-bg-success' : 'text-bg-danger';
                    $text = $value ? 'Actif' : 'Inactif';

                    return '<span class="badge '.$badgeClass.'">'.$text.'</span>';
                })
                ->html(),

            Column::make('Date de création', 'created_at')
                ->sortable()
                ->format(fn ($value) => $value->format('d/m/Y H:i'))
                ->deselected(),

            Column::make('Action', 'id')
                ->excludeFromColumnSelect()
                ->format(function ($value, $row) {
                    return view('partials.neighborhoods.actions', ['neighborhood' => $row]);
                }),
        ];
    }

    public function filters(): array
    {
        return [
            SelectFilter::make('Ville')
                ->options(['' => 'Toutes les villes'] + City::orderBy('name')->pluck('name', 'id')->toArray())
                ->filter(function (Builder $builder, string $value) {
                    if ($value === '') {
                        return $builder;
                    }

                    return $builder->whereHas('municipality', function ($query) use ($value) {
                        $query->where('city_id', $value);
                    });
                }),

            SelectFilter::make('Municipalité')
                ->options(['' => 'Toutes les municipalités'] + Municipality::orderBy('name')->pluck('name', 'id')->toArray())
                ->filter(function (Builder $builder, string $value) {
                    if ($value === '') {
                        return $builder;
                    }

                    return $builder->where('municipality_id', $value);
                }),

            SelectFilter::make('Statut')
                ->options([
                    '' => 'Tous',
                    '1' => 'Actif',
                    '0' => 'Inactif',
                ])
                ->filter(function (Builder $builder, string $value) {
                    if ($value !== '') {
                        $builder->where('is_active', (bool) $value);
                    }
                }),
        ];
    }

    public function builder(): Builder
    {
        return Neighborhood::query()->with(['municipality.city']);
    }

    /**
     * Toggle the active status of a neighborhood
     */
    public function toggleNeighborhoodStatus($neighborhoodId)
    {
        try {
            $neighborhood = Neighborhood::find($neighborhoodId);

            if (! $neighborhood) {
                $this->dispatch('show-notification', [
                    'type' => 'error',
                    'title' => 'Erreur !',
                    'message' => "Le quartier sélectionné n'existe pas.",
                    'timer' => 3000,
                ]);

                return;
            }

            $currentStatus = $neighborhood->is_active;
            $newStatus = ! $currentStatus;

            $neighborhood->is_active = $newStatus;
            $result = $neighborhood->save();

            if ($result) {
                $status = $newStatus ? 'activé' : 'désactivé';
                $name = $neighborhood->name;

                $this->dispatch('show-notification', [
                    'type' => 'success',
                    'title' => 'Statut modifié !',
                    'message' => "Le quartier {$name} a été {$status} avec succès.",
                    'timer' => 3000,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error toggling neighborhood status: '.$e->getMessage());

            $this->dispatch('show-notification', [
                'type' => 'error',
                'title' => 'Erreur !',
                'message' => "Une erreur s'est produite lors de la modification du statut du quartier.",
                'timer' => 3000,
            ]);
        }
    }

    /**
     * Delete a neighborhood
     */
    public function deleteNeighborhood($neighborhoodId)
    {
        try {
            $neighborhoodService = app(NeighborhoodService::class);
            $neighborhood = $neighborhoodService->find($neighborhoodId);
            $name = $neighborhood->name;

            $neighborhoodService->delete($neighborhood);

            $this->dispatch('show-notification', [
                'type' => 'success',
                'title' => 'Quartier supprimé !',
                'message' => "Le quartier {$name} a été supprimé avec succès.",
                'timer' => 3000,
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting neighborhood: '.$e->getMessage());

            $this->dispatch('show-notification', [
                'type' => 'error',
                'title' => 'Erreur !',
                'message' => "Une erreur s'est produite lors de la suppression du quartier.",
                'timer' => 3000,
            ]);
        }
    }
}
