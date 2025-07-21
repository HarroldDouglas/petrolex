<?php

namespace App\Livewire\Bottle;

use App\Models\Geography\City;
use App\Models\Geography\Country;
use App\Repositories\Geography\GeographyRepositoryInterface;
use App\Services\BottleType\BottleTypeService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Http\FormRequest;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

abstract class AbstractBottleTypeForm extends Component
{
    use WithFileUploads;

    public $name;
    public $weight;
    public $capacity;
    public $content_price;
    public $bottle_with_content_price;
    public array $product_images = [];
    public $description;
    public $existingImages = [];
    public $imagesIdsToDelete = [];

    public $cityPrices = [];
    public Collection $availableCountries;
    public array $availableCities;
    public $selectedCountryId = null;
    public $selectedCityId = null;
    public $tempCityContentPrice = null;
    public $tempCityContentWithBottlePrice = null;

    public bool $is_active = true;

    protected $bottleTypeService;
    protected GeographyRepositoryInterface $geographyRepository;

    public function boot(
        BottleTypeService $bottleTypeService,
        GeographyRepositoryInterface $geographyRepository,
    ) {
        $this->bottleTypeService = $bottleTypeService;
        $this->geographyRepository = $geographyRepository;
    }

    public function mount()
    {
        $this->availableCountries = $this->geographyRepository->getAllCountries();

        if (is_null($this->selectedCountryId) && $this->availableCountries->isNotEmpty()) {
            /** @var Country $country */
            $country = $this->availableCountries->first();
            $this->selectedCountryId = $country->id;
        }

        if ($this->selectedCountryId) {
            $this->availableCities = $this->geographyRepository->getCitiesByCountryId($this->selectedCountryId)->pluck('name', 'id')->toArray();
            if (is_null($this->selectedCityId) && ! empty($this->availableCities)) {
                $this->selectedCityId = array_key_first($this->availableCities);
            }
        }
    }

    public function updatedSelectedCountryId($value)
    {
        $this->selectedCityId = null;
        $this->availableCities = [];

        if ($value) {
            $this->availableCities = $this->geographyRepository->getCitiesByCountryId($value)->pluck('name', 'id')->toArray();
            if (! empty($this->availableCities)) {
                $this->selectedCityId = array_key_first($this->availableCities);
            }
        }
    }

    public function isCityPriceAddButtonDisabled(): bool
    {
        if (is_null($this->selectedCityId) || ! is_numeric($this->tempCityContentPrice) || ! is_numeric($this->tempCityContentWithBottlePrice)) {
            return true;
        }

        $contentPrice = (float) $this->tempCityContentPrice;
        $contentWithBottlePrice = (float) $this->tempCityContentWithBottlePrice;

        if ($contentPrice <= 0 || $contentWithBottlePrice <= 0 || $contentWithBottlePrice <= $contentPrice) {
            return true;
        }

        return false;
    }

    public function addCityPrice()
    {
        /** @var City $selectedCity */
        $selectedCity = $this->geographyRepository->getCitiesByCountryId($this->selectedCountryId)
            ->where('id', $this->selectedCityId)
            ->first();
        /** @var Country $selectedCountry */
        $selectedCountry = $this->geographyRepository->getAllCountries()
            ->where('id', $this->selectedCountryId)
            ->first();

        $this->cityPrices[] = [
            'city_id' => $this->selectedCityId,
            'city_name' => $selectedCity->name ?? 'N/A',
            'country_id' => $this->selectedCountryId,
            'country_name' => $selectedCountry->name ?? 'N/A',
            'content_price' => (float) $this->tempCityContentPrice,
            'content_with_bottle_price' => (float) $this->tempCityContentWithBottlePrice,
        ];

        $this->selectedCityId = null;
        $this->tempCityContentPrice = null;
        $this->tempCityContentWithBottlePrice = null;
    }

    public function updateCityPrice($index, $field, $value)
    {
        $this->cityPrices[$index][$field] = (float) $value;
    }

    public function removeCityPrice($index)
    {
        unset($this->cityPrices[$index]);
        $this->cityPrices = array_values($this->cityPrices);
    }

    public function deleteImage($imageId)
    {
        $this->imagesIdsToDelete[] = $imageId;
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
        return view('livewire.bottle.bottle-type-form', [
            'existingImages' => $this->existingImages,
            'availableCountries' => $this->availableCountries,
            'availableCities' => $this->availableCities,
        ]);
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['weight'])) {
            $this->generateName();
        }

        $this->validateOnly($propertyName);
    }

    protected function generateName()
    {
        $this->name = 'Bouteille  de '.$this->weight.' Kg';
    }

    abstract public function save();
}
