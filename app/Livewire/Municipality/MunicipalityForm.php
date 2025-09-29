<?php

namespace App\Livewire\Municipality;

use App\Models\Geography\Municipality;
use App\Services\Geography\CityService;
use App\Services\Geography\MunicipalityService;
use App\Services\Geography\NeighborhoodService;
use Livewire\Component;

class MunicipalityForm extends Component
{
    public ?Municipality $municipality = null;

    public ?string $name = '';

    public ?int $cityId = null;

    public array $selectedNeighborhoods = [];

    public array $cities = [];

    public array $neighborhoods = [];

    private CityService $cityService;
    private NeighborhoodService $neighborhoodService;
    private MunicipalityService $municipalityService;

    public function boot()
    {
        $this->cityService = app(CityService::class);
        $this->neighborhoodService = app(NeighborhoodService::class);
        $this->municipalityService = app(MunicipalityService::class);
    }

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

    public function mount(?Municipality $municipality = null)
    {
        $this->municipality = $municipality;

        if ($this->municipality) {
            $this->name = $this->municipality->name ?? '';
            $this->cityId = $this->municipality->city_id;
            $this->selectedNeighborhoods = $this->municipality->neighborhoods->pluck('id')->toArray();
        }

        $this->cities = $this->cityService->getCitiesByCountry('1')->map(function($city) {
            return [
                'id' => $city->id,
                'name' => $city->name
            ];
        })->toArray();

        if ($this->cityId) {
            $this->loadNeighborhoods();
        }
    }

    public function updatedCityId($value)
    {
        // Clear previously selected neighborhoods when city changes
        $this->selectedNeighborhoods = [];
        
        // Reset neighborhoods array first
        $this->neighborhoods = [];
        
        // Load neighborhoods for the new city
        $this->loadNeighborhoods();
    }

    public function loadNeighborhoods()
    {
        if ($this->cityId) {
            $neighborhoods = $this->neighborhoodService->getNeighborhoodsByCity($this->cityId);
            $this->neighborhoods = $neighborhoods->map(function($neighborhood) {
                return [
                    'id' => $neighborhood->id,
                    'name' => $neighborhood->name
                ];
            })->toArray();
            
            // Log for debugging
            \Illuminate\Support\Facades\Log::info("Loaded neighborhoods for city {$this->cityId}: " . count($this->neighborhoods));
        } else {
            $this->neighborhoods = [];
        }
        
        // Clear selected neighborhoods when city changes
        $this->selectedNeighborhoods = [];
        
        // Emit event to reinitialize Select2
        $this->dispatch('neighborhoodsUpdated', ['count' => count($this->neighborhoods)]);
        
        // Flash message for user feedback
        if (count($this->neighborhoods) > 0) {
            session()->flash('info', count($this->neighborhoods) . ' quartier(s) chargé(s) pour cette ville.');
        }
    }

    public function saveMunicipality()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'city_id' => $this->cityId,
        ];

        // Determine if this is an update or create operation
        // Update only if municipality exists AND has a valid ID
        if ($this->municipality && $this->municipality->exists && $this->municipality->id) {
            $this->municipalityService->updateMunicipality($this->municipality, $data, $this->selectedNeighborhoods);
            session()->flash('success', 'Municipalité mise à jour avec succès.');
        } else {
            // Create new municipality
            $this->municipalityService->createMunicipality($data, $this->selectedNeighborhoods);
            session()->flash('success', 'Municipalité créée avec succès.');
            $this->reset('name', 'cityId', 'selectedNeighborhoods'); // Clear form after creation
        }

        return redirect()->route('municipalities.index');
    }

    public function getMunicipalityStatusProperty()
    {
        if (!$this->municipality) {
            return 'NULL - Create mode';
        }
        
        if (!$this->municipality->exists) {
            return 'EXISTS: FALSE - Create mode';
        }
        
        if (!$this->municipality->id) {
            return 'ID: NULL - Create mode';
        }
        
        return 'ID: ' . $this->municipality->id . ' - Update mode';
    }

    public function render()
    {
        return view('livewire.municipality.municipality-form');
    }
}
