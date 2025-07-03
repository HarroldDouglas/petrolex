<?php

namespace App\Livewire\Bottle;

use App\Services\BottleType\BottleTypeService;
use App\Services\Geography\StaticGeographyService;
use App\Services\Shared\Media\MediaServiceInterface;
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
    protected $mediaService;

    public function boot(
        BottleTypeService $bottleTypeService,
        StaticGeographyService $geographyService,
        MediaServiceInterface $mediaService
    ) {
        $this->bottleTypeService = $bottleTypeService;
        $this->geographyService = $geographyService;
        $this->mediaService = $mediaService;
    }

    public function addCityPrice()
    {
        if (! $this->selectedCity || $this->tempCityContentPrice === '' || $this->tempCityContentWithBottlePrice === '') {
            session()->flash('error', 'Veuillez sélectionner une ville et renseigner les prix avant d\'ajouter.');

            return;
        }

        // Vérifier si cette ville existe déjà
        if (in_array($this->selectedCity, array_column($this->cityPrices, 'city'))) {
            session()->flash('error', 'Cette ville a déjà un prix spécifique défini.');

            return;
        }

        // Ajout d'un nouveau prix
        $this->cityPrices[] = [
            'city' => $this->selectedCity,
            'content_price' => $this->tempCityContentPrice,
            'content_with_bottle_price' => $this->tempCityContentWithBottlePrice,
        ];

        $this->selectedCity = '';
        $this->tempCityContentPrice = '';
        $this->tempCityContentWithBottlePrice = '';
    }

    public function updateCityPrice($index, $field, $value)
    {
        $this->cityPrices[$index][$field] = $value;
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

    // A helper method to generate the name string
    protected function generateName()
    {
        $this->name = 'Bouteille  de '.$this->weight.' Kg';
    }

    abstract public function save();
}
