<?php

namespace App\Livewire\Bottle;

use App\Services\BottleType\BottleTypeService;
use App\Services\Geography\StaticGeographyService;
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
    public $availableCities = [];
    public $selectedCity = '';
    public $tempCityContentPrice = '';
    public $tempCityContentWithBottlePrice = '';

    public bool $is_active = true;

    protected $bottleTypeService;
    protected $geographyService;

    public function boot(
        BottleTypeService $bottleTypeService,
        StaticGeographyService $geographyService,
    ) {
        $this->bottleTypeService = $bottleTypeService;
        $this->geographyService = $geographyService;
    }

    public function isCityPriceAddButtonDisabled(): bool
    {
        if (empty($this->selectedCity) || empty($this->tempCityContentPrice) || empty($this->tempCityContentWithBottlePrice)) {
            return true;
        }

        if ((float) $this->tempCityContentWithBottlePrice <= (float) $this->tempCityContentPrice) {
            return true;
        }

        return false;
    }

    public function addCityPrice()
    {
        if (! $this->selectedCity || $this->tempCityContentPrice === '' || $this->tempCityContentWithBottlePrice === '') {
            session()->flash('error', 'Veuillez sélectionner une ville et renseigner les prix avant d\'ajouter.');

            return;
        }

        $this->cityPrices[] = [
            'city' => $this->selectedCity,
            'content_price' => (float) $this->tempCityContentPrice,
            'content_with_bottle_price' => (float) $this->tempCityContentWithBottlePrice,
        ];

        $this->selectedCity = '';
        $this->tempCityContentPrice = '';
        $this->tempCityContentWithBottlePrice = '';
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
