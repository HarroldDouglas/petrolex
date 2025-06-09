<?php

namespace App\Livewire\DistributionCenter;

use App\Services\Geography\GeographyServiceInterface;
use Illuminate\Foundation\Http\FormRequest;
use Livewire\Component;

abstract class AbstractDistributionCenterForm extends Component
{
    public $name;
    public $country = 'Cameroun';
    public $city;
    public $neighborhood;
    public $address;
    public $phone;
    public $email;
    public $latitude;
    public $longitude;
    public $storage_capacity;
    public $is_active = 1;
    public $description;

    /** @var GeographyServiceInterface */
    protected $geographyService;

    public function boot(GeographyServiceInterface $geographyService)
    {
        $this->geographyService = $geographyService;
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

    /**
     * Get the request class for validation
     */
    abstract protected function customRequest(): FormRequest;

    public function render()
    {
        return view('livewire.distribution-center.form', [
            'cities' => $this->geographyService->getCities($this->country),
            'neighborhoods' => $this->city ? $this->geographyService->getNeighborhoods($this->city) : [],
            'countries' => $this->geographyService->getCountries(),
        ]);
    }

    /**
     * Real-time validation for each field
     */
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    abstract public function submit();
}
