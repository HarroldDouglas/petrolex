<?php

namespace App\Livewire\Municipality;

use App\Models\Geography\Municipality;
use App\Services\Geography\CityService;
use App\Services\Geography\MunicipalityService;
use App\Services\Geography\NeighborhoodService;
use Illuminate\Foundation\Http\FormRequest;
use Livewire\Component;

abstract class AbstractMunicipalityForm extends Component
{
    public ?Municipality $municipality = null;
    public ?string $name = '';
    public ?int $cityId = null;
    public array $selectedNeighborhoods = [];
    public array $cities = [];
    public array $neighborhoods = [];

    protected CityService $cityService;
    protected NeighborhoodService $neighborhoodService;
    protected MunicipalityService $municipalityService;

    protected $listeners = [
        'neighborhoods:selection-changed' => 'updateNeighborhoods',
    ];

    public function boot(
        CityService $cityService,
        NeighborhoodService $neighborhoodService,
        MunicipalityService $municipalityService
    ) {
        $this->cityService = $cityService;
        $this->neighborhoodService = $neighborhoodService;
        $this->municipalityService = $municipalityService;
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
            $this->loadNeighborhoods();
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
        $this->selectedNeighborhoods = [];

        $this->neighborhoods = [];

        $this->loadNeighborhoods();
    }

    public function loadNeighborhoods()
    {

        if (! $this->cityId) {
            $this->neighborhoods = [];

            return;
        }

        $this->neighborhoods = $this->neighborhoodService
            ->getNeighborhoodsByCity($this->cityId)
            ->pluck('name', 'id')
            ->map(fn ($name, $id) => ['id' => $id, 'name' => $name])
            ->values()
            ->toArray();

        $count = count($this->neighborhoods);

        $this->dispatch('neighborhoodsUpdated', ['count' => $count]);

        if ($count > 0) {
            session()->flash('info', "{$count} quartier(s) chargé(s) pour cette ville.");
        }
    }

    public function getAvailableNeighborhoodsProperty()
    {
        $options = [];
        foreach ($this->neighborhoods as $neighborhood) {
            $options[$neighborhood['id']] = $neighborhood['name'];
        }

        return $options;
    }

    public function updateNeighborhoods($data)
    {
        $this->selectedNeighborhoods = $data['selectedOptions'] ?? [];
    }

    public function render()
    {
        return view('livewire.municipality.form');
    }

    abstract public function submit();
}
