<?php

namespace App\Livewire\Neighborhood;

use App\Models\Geography\Municipality;
use App\Models\Geography\Neighborhood;
use App\Services\Geography\CityService;
use App\Services\Geography\MunicipalityService;
use App\Services\Geography\NeighborhoodService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Http;
use Livewire\Component;

abstract class AbstractNeighborhoodForm extends Component
{
    public ?Neighborhood $neighborhood = null;
    public ?string $name = '';
    public ?int $cityId = null;
    public ?int $municipalityId = null;
    public ?string $latitude = null;
    public ?string $longitude = null;
    public bool $is_active = true;
    public array $cities = [];
    public array $municipalities = [];
    public bool $hasPolygon = false;
    public bool $fetchingPolygon = false;
    public ?string $polygonMessage = null;

    protected CityService $cityService;
    protected MunicipalityService $municipalityService;
    protected NeighborhoodService $neighborhoodService;

    public function boot(
        CityService $cityService,
        MunicipalityService $municipalityService,
        NeighborhoodService $neighborhoodService
    ) {
        $this->cityService = $cityService;
        $this->municipalityService = $municipalityService;
        $this->neighborhoodService = $neighborhoodService;
    }

    public function initialize()
    {
        $this->cities = $this->cityService->getCitiesByCountry('1')->map(function ($city) {
            return [
                'id' => $city->id,
                'name' => $city->name,
            ];
        })->toArray();

        if ($this->cityId) {
            $this->loadMunicipalities();
        }
    }

    public function rules()
    {
        // @phpstan-ignore-next-line
        return $this->customRequest()->rules();
    }

    public function messages()
    {
        return $this->customRequest()->messages();
    }

    abstract protected function customRequest(): FormRequest;

    public function updatedCityId($value)
    {
        $this->municipalityId = null;
        $this->municipalities = [];
        $this->loadMunicipalities();
    }

    public function loadMunicipalities()
    {
        if (! $this->cityId) {
            $this->municipalities = [];

            return;
        }

        $this->municipalities = Municipality::where('city_id', $this->cityId)
            ->orderBy('name', 'asc')
            ->get()
            ->map(fn ($municipality) => ['id' => $municipality->id, 'name' => $municipality->name])
            ->toArray();

        $count = count($this->municipalities);

        if ($count > 0) {
            session()->flash('info', "{$count} municipalité(s) chargée(s) pour cette ville.");
        }
    }

    public function render()
    {
        return view('livewire.neighborhood.form');
    }

    public function fetchPolygonFromOsm(): void
    {
        if (empty($this->name)) {
            $this->polygonMessage = 'error:Veuillez d\'abord saisir le nom du quartier.';

            return;
        }

        $this->fetchingPolygon = true;
        $this->polygonMessage = null;

        $cityName = '';
        if ($this->cityId) {
            $city = collect($this->cities)->firstWhere('id', $this->cityId);
            $cityName = $city['name'] ?? '';
        }

        $searchQuery = $cityName
            ? "{$this->name}, {$cityName}, Cameroon"
            : "{$this->name}, Cameroon";

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Petrolex/1.0 (contact@isogaz.net)',
            ])->get('https://nominatim.openstreetmap.org/search', [
                'q' => $searchQuery,
                'format' => 'geojson',
                'polygon_geojson' => 1,
                'limit' => 1,
            ]);

            $features = $response->ok() ? $response->json('features') : [];

            if (! empty($features)) {
                $geometry = $features[0]['geometry'] ?? null;

                /* Auto-fill missing coordinates from the OSM bbox center so an
                   admin can't save a neighborhood without them (null coords
                   crash the strongly-typed mobile apps). */
                $bbox = $features[0]['bbox'] ?? null;
                if ($bbox && count($bbox) === 4 && ($this->latitude === null || $this->latitude === '')) {
                    $this->longitude = (string) round(($bbox[0] + $bbox[2]) / 2, 8);
                    $this->latitude = (string) round(($bbox[1] + $bbox[3]) / 2, 8);
                }

                if ($geometry && in_array($geometry['type'], ['Polygon', 'MultiPolygon'])) {
                    if ($this->neighborhood) {
                        $this->neighborhood->update(['polygon' => $geometry]);
                    }
                    $this->hasPolygon = true;
                    $this->polygonMessage = 'success:Polygone récupéré avec succès depuis OpenStreetMap.';
                } else {
                    $this->polygonMessage = 'warning:Aucun polygone trouvé pour ce quartier sur OpenStreetMap.';
                }
            } else {
                $this->polygonMessage = 'warning:Aucun résultat trouvé sur OpenStreetMap pour "'.$searchQuery.'".';
            }
        } catch (\Exception $e) {
            $this->polygonMessage = 'error:Erreur lors de la récupération : '.$e->getMessage();
        }

        $this->fetchingPolygon = false;
    }

    abstract public function submit();
}
