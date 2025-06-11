<?php

namespace App\Livewire\Bottle;

use App\Services\BottleType\BottleTypeService;
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
    public $product_images;
    public $description;

    public $cityPrices = [];
    public $availableCities = [];
    public $selectedCity = '';

    public bool $is_active = true;

    protected $bottleTypeService;

    public function boot(BottleTypeService $bottleTypeService)
    {
        $this->bottleTypeService = $bottleTypeService;
    }

    public function addCityPrice()
    {
        if (! $this->selectedCity
            || in_array($this->selectedCity,
                array_column($this->cityPrices, 'city'))) {
            return;
        }

        $this->cityPrices[] = [
            'city' => $this->selectedCity,
            'bottle_type_id' => null,
            'content_price' => '',
            'content_with_bottle_price' => '',
        ];

        $this->selectedCity = '';
    }

    public function removeCityPrice($index)
    {
        unset($this->cityPrices[$index]);
        $this->cityPrices = array_values($this->cityPrices);
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
        return view('livewire.bottle.bottle-type-form');
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
