<?php

namespace App\Livewire\DistributionCenter;

use App\Models\Geography\Country;
use App\Repositories\Geography\GeographyRepositoryInterface;
use App\Services\Geography\GoogleMapsLinkParser;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Http\FormRequest;
use Livewire\Component;

abstract class AbstractDistributionCenterForm extends Component
{
    public $name;
    public $country_id = null;
    public $city_id = null;
    public $neighborhood_id = null;
    public $address;
    public $phone;
    public $email;
    public $latitude;
    public $longitude;
    public $storage_capacity;
    public $is_active = 1;
    public $description;
    public ?string $mapsLink = null;
    public ?string $mapsLinkMessage = null;

    public Collection $availableCountries;
    public Collection $availableCities;
    public Collection $availableNeighborhoods;

    protected GeographyRepositoryInterface $geographyRepository;

    public function boot(GeographyRepositoryInterface $geographyRepository)
    {
        $this->geographyRepository = $geographyRepository;
        $this->availableCountries = new Collection;
        $this->availableCities = new Collection;
        $this->availableNeighborhoods = new Collection;

        $this->availableCountries = $this->geographyRepository->getAllCountries();

        if (is_null($this->country_id) && $this->availableCountries->isNotEmpty()) {
            /** @var Country $country */
            $country = $this->availableCountries->first();
            $this->country_id = $country->id;
        }

        if ($this->country_id) {
            $this->availableCities = $this->geographyRepository->getCitiesByCountryId($this->country_id);
        }
        if ($this->city_id) {
            $this->availableNeighborhoods = $this->geographyRepository->getNeighborhoodsByCityId($this->city_id);
        }
    }

    /** Fills lat/lng from a pasted Google Maps link and re-centers the map marker. */
    public function extractCoordinates(GoogleMapsLinkParser $parser): void
    {
        $coordinates = $parser->parse($this->mapsLink);

        if ($coordinates === null) {
            $this->mapsLinkMessage = 'error:Aucune coordonnée trouvée dans ce lien. '
                .'Sur Google Maps, faites un clic droit sur le point puis « Copier les coordonnées ».';

            return;
        }

        $this->latitude = (string) $coordinates['latitude'];
        $this->longitude = (string) $coordinates['longitude'];
        $this->mapsLinkMessage = 'success:Coordonnées extraites : '
            .$coordinates['latitude'].', '.$coordinates['longitude'];

        $this->resetValidation(['latitude', 'longitude']);

        $this->dispatch(
            'coordinates-extracted',
            lat: $coordinates['latitude'],
            lng: $coordinates['longitude'],
        );
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

    public function render()
    {
        return view('livewire.distribution-center.form', [
            'countries' => $this->availableCountries,
            'cities' => $this->availableCities,
            'neighborhoods' => $this->availableNeighborhoods,
        ]);
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);

        if ($propertyName === 'country_id') {
            $this->city_id = null;
            $this->neighborhood_id = null;
            $this->availableCities = $this->geographyRepository->getCitiesByCountryId($this->country_id);
            $this->availableNeighborhoods = new Collection;
        } elseif ($propertyName === 'city_id') {
            $this->neighborhood_id = null;
            $this->availableNeighborhoods = new Collection;
            if ($this->city_id) {
                $this->availableNeighborhoods = $this->geographyRepository->getNeighborhoodsByCityId($this->city_id);
            }
        }
    }

    abstract public function submit();
}
