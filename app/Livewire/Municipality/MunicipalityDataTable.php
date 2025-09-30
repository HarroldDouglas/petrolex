<?php

namespace App\Livewire\Municipality;

use App\Models\Geography\City;
use App\Models\Geography\Municipality;
use App\Services\Geography\MunicipalityService;
use HarroldWafo\LaravelCustomDatatable\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Rappasoft\LaravelLivewireTables\Views\Column;

class MunicipalityDataTable extends BaseDataTable
{
    protected $model = Municipality::class;

    protected const DEFAULT_SORT_FIELD = 'created_at';
    protected const DEFAULT_SORT_DIRECTION = 'desc';

    protected function getExportFileName(): string
    {
        return 'municipalites';
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

            Column::make('Ville', 'city.name')
                ->sortable(function (Builder $query, string $direction) {
                    return $query->orderBy(City::select('name')->whereColumn('cities.id', 'municipalities.city_id'), $direction);
                })
                ->searchable(function (Builder $query, string $searchTerm) {
                    $query->orWhereHas('city', function (Builder $query) use ($searchTerm) {
                        $query->where('name', 'like', '%'.$searchTerm.'%');
                    });
                }),

            Column::make('Quartiers')
                ->label(function ($row) {
                    $count = $row->neighborhoods_count ?? 0;
                    $badgeClass = $count > 0 ? 'bg-primary' : 'bg-secondary';
                    return new HtmlString(
                        '<span class="badge '.$badgeClass.'">'.$count.' quartier(s)</span>'
                    );
                }),

            Column::make('Date de création', 'created_at')
                ->sortable()
                ->format(fn ($value) => $value->format('d/m/Y')),

            Column::make('Actions')
                ->label(
                    function ($row) {
                        return view('partials.municipalities.actions', ['municipality' => $row]);
                    }
                ),
        ];
    }

    public function builder(): Builder
    {
        return Municipality::query()
            ->with(['city', 'neighborhoods'])
            ->withCount('neighborhoods');
    }

    public function deleteMunicipality(int $municipalityId): void
    {
        try {
            $municipalityService = app(MunicipalityService::class);
            $municipality = $municipalityService->find($municipalityId);
            $municipalityService->deleteMunicipality($municipality);
            
            $this->notify('Municipalité supprimée avec succès.', 'success');
        } catch (\Exception $e) {
            $this->notify('Erreur lors de la suppression de la municipalité.', 'error');
        }
    }
}