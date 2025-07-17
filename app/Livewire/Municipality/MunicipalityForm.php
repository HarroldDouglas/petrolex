<?php

namespace App\Livewire\Municipality;

use App\Models\City;
use App\Models\Municipality;
use App\Models\Neighborhood;
use App\Services\Geography\CityService;
use App\Services\Geography\MunicipalityService;
use App\Services\Geography\NeighborhoodService;
use Livewire\Component;

class MunicipalityForm extends Component
{
    public ?Municipality $municipality = null;

    public string $name = '';

    public ?int $cityId = null;

    public array $selectedNeighborhoods = [];

    public array $cities = [];

    public array $neighborhoods = [];

    protected $rules = [
        'name' => 'required|string|max:255',
        'cityId' => 'required|exists:cities,id',
        'selectedNeighborhoods' => 'array',
        'selectedNeighborhoods.*' => 'exists:neighborhoods,id',
    ];

    protected $messages = [
        'name.required' => 'Le nom de la municipalité est requis.',
        'cityId.required' => 'La ville est requise.',
        'cityId.exists' => 'La ville sélectionnée est invalide.',
        'selectedNeighborhoods.*.exists' => 'Un ou plusieurs quartiers sélectionnés sont invalides.',
    ];

    public function boot(CityService $cityService, NeighborhoodService $neighborhoodService, MunicipalityService $municipalityService)
    {
        $this->cityService = $cityService;
        $this->neighborhoodService = $neighborhoodService;
        $this->municipalityService = $municipalityService;
    }

    public function mount(?Municipality $municipality = null)
    {
        $this->municipality = $municipality;

        if ($this->municipality) {
            $this->name = $this->municipality->name;
            $this->cityId = $this->municipality->city_id;
            $this->selectedNeighborhoods = $this->municipality->neighborhoods->pluck('id')->toArray();
        }

        // Load cities for Cameroon (assuming Cameroon is the default country)
        $this->cities = $this->cityService->getCitiesByCountry('Cameroon')->toArray();

        if ($this->cityId) {
            $this->loadNeighborhoods();
        }
    }

    public function updatedCityId($value)
    {
        $this->selectedNeighborhoods = []; // Reset selected neighborhoods when city changes
        $this->loadNeighborhoods();
    }

    private function loadNeighborhoods()
    {
        if ($this->cityId) {
            $this->neighborhoods = $this->neighborhoodService->getNeighborhoodsByCity($this->cityId)->toArray();
        } else {
            $this->neighborhoods = [];
        }
    }

    public function saveMunicipality()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'city_id' => $this->cityId,
        ];

        if ($this->municipality) {
            $this->municipalityService->updateMunicipality($this->municipality, $data, $this->selectedNeighborhoods);
            session()->flash('success', 'Municipalité mise à jour avec succès.');
        } else {
            $this->municipalityService->createMunicipality($data, $this->selectedNeighborhoods);
            session()->flash('success', 'Municipalité créée avec succès.');
            $this->reset('name', 'cityId', 'selectedNeighborhoods'); // Clear form after creation
        }

        return redirect()->route('municipalities.index');
    }

    public function render()
    {
        return view('livewire.municipality.municipality-form');
    }
}
