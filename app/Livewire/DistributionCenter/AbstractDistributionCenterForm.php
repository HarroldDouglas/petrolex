<?php

namespace App\Livewire\DistributionCenter;

use App\Models\Geography\Country;
use App\Repositories\Geography\GeographyRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Http\FormRequest;
use Livewire\Component;

abstract class AbstractDistributionCenterForm extends Component
{
    public $name;
    public $countryId = null;
    public $cityId = null;
    public $neighborhoodId = null;
    public $address;
    public $phone;
    public $email;
    public $latitude;
    public $longitude;
    public $storage_capacity;
    public $is_active = 1;
    public $description;

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

        if (is_null($this->countryId) && $this->availableCountries->isNotEmpty()) {
            /** @var Country $country */
            $country = $this->availableCountries->first();
            $this->countryId = $country->id;
        }

        // Load initial cities and neighborhoods based on current IDs
        if ($this->countryId) {
            $this->availableCities = $this->geographyRepository->getCitiesByCountryId($this->countryId);
        }
        if ($this->cityId) {
            $this->availableNeighborhoods = $this->geographyRepository->getNeighborhoodsByCityId($this->cityId);
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

        if ($propertyName === 'countryId') {
            $this->cityId = null;
            $this->neighborhoodId = null;
            $this->availableCities = $this->geographyRepository->getCitiesByCountryId($this->countryId);
            $this->availableNeighborhoods = new Collection;
        } elseif ($propertyName === 'cityId') {
            $this->neighborhoodId = null;
            $this->availableNeighborhoods = $this->geographyRepository->getNeighborhoodsByCityId($this->cityId);
        }
    }

    abstract public function submit();
}
