<?php

namespace App\Livewire\Components;

use Livewire\Component;

class RouteMap extends Component
{
    /**
     * Points d'origine et de destination (constantes)
     */
    const POINT_A_LAT = 3.8775; // Yaoundé, point A
    const POINT_A_LNG = 11.5468;
    const POINT_A_NAME = 'Dépôt central Petrolex';

    const POINT_B_LAT = 3.8615; // Point B (à ~2km du Point A)
    const POINT_B_LNG = 11.5208;
    const POINT_B_NAME = 'Centre de distribution Nlongkak';

    /**
     * Mode de transport fixé à 'driving' (voiture)
     * Autres modes possibles (non utilisés dans l'interface):
     * - 'DRIVING' : voiture (par défaut)
     * - 'TWO_WHEELER' : moto
     * - 'BICYCLING' : vélo
     * - 'TRANSIT' : transport en commun
     * - 'WALKING' : marche
     */
    public $transportMode = 'DRIVING';

    /**
     * Résultats de l'itinéraire
     */
    public $distance = null;
    public $duration = null;
    public $steps = [];

    /**
     * Paramètres de la carte
     */
    public $defaultZoom = 13;

    /**
     * Clé API Google Maps
     * Note importante: cette clé est utilisée uniquement pour les démonstrations et les tests
     * Pour un environnement de production, utilisez une clé spécifique à votre domaine
     * configurée dans vos variables d'environnement
     */
    protected function getGoogleMapsApiKey()
    {
        // En production, utilisez la clé depuis les variables d'environnement
        return config('services.google_maps.api_key', 'AIzaSyB41DRUbKWJHPxaFjMAwdrzWzbVKartNGg');
    }

    /**
     * Rendu du composant
     */
    public function render()
    {
        return view('livewire.components.route-map', [
            'apiKey' => $this->getGoogleMapsApiKey(),
            'pointA' => [
                'lat' => self::POINT_A_LAT,
                'lng' => self::POINT_A_LNG,
                'name' => self::POINT_A_NAME,
            ],
            'pointB' => [
                'lat' => self::POINT_B_LAT,
                'lng' => self::POINT_B_LNG,
                'name' => self::POINT_B_NAME,
            ],
        ]);
    }
}
