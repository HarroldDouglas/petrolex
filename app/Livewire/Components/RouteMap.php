<?php

namespace App\Livewire\Components;

use Livewire\Component;

class RouteMap extends Component
{
    public $apiKey;
    public $pointA;
    public $pointB;
    public $isMapReady = false;

    /**
     * Demo coordinates for map route display
     * Note: These values are for UI mockup purposes only.
     * In the final project, these should be replaced with dynamic data.
     */
    const POINT_A = [
        'lat'  => 3.8922476106410713,
        'lng'  => 11.547542478352296,
        'name' => 'Petrolex Ngousso'
    ];

    const POINT_B = [
        'lat'  => 3.848035597643558,
        'lng'  => 11.482854013491417,
        'name' => 'Petrolex Carrefour Etoug-ebe, Station'
    ];

    /**
     * Default transport mode
     */
    public $transportMode = 'DRIVING';

    /**
     * Route calculations results
     */
    public $distance = null;
    public $duration = null;
    public $steps = [];

    /**
     * Map parameters
     */
    public $defaultZoom = 13;

    public function mount()
    {
        $this->apiKey = $this->getGoogleMapsApiKey();
        $this->pointA = self::POINT_A;
        $this->pointB = self::POINT_B;
    }

    protected function getGoogleMapsApiKey()
    {
        return config('services.google.maps.api_key');
    }

    public function setMapReady()
    {
        $this->isMapReady = true;
        $this->dispatch('map-ready');
    }

    public function render()
    {
        return view('livewire.components.route-map');
    }
}
