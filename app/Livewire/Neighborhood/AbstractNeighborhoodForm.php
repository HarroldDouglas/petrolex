<?php

namespace App\Livewire\Neighborhood;

use App\Models\Geography\City;
use App\Models\Geography\Municipality;
use App\Models\Geography\Neighborhood;
use App\Services\Geography\CityService;
use App\Services\Geography\MunicipalityService;
use App\Services\Geography\NeighborhoodService;
use Illuminate\Foundation\Http\FormRequest;
use Livewire\Component;

abstract class AbstractNeighborhoodForm extends Component
{
    public ?Neighborhood $neighborhood = null;
    public ?string $name = '';
    public ?int $cityId = null;
    public ?int $municipalityId = null;
    public bool $is_active = true;
    public array $cities = [];
    public array $municipalities = [];

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

    abstract public function submit();
}
