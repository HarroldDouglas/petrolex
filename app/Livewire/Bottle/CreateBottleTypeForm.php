<?php

namespace App\Livewire\Bottle;

use App\Models\DistributionCenter;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateBottleTypeForm extends Component
{
    use WithFileUploads;

    // Base bottle type information
    public $type_name;
    public $bottle_capacity_price;
    public $bottle_price;
    public $product_images;
    public $description;

    // City-specific pricing
    public $cityPrices = [];
    public $availableCities = [];
    public $selectedCity = '';

    public function mount()
    {
        // Load all available cities from distribution centers
        $this->availableCities = DistributionCenter::distinct()
            ->pluck('city')
            ->toArray();
    }

    public function addCityPrice()
    {
        if (! $this->selectedCity || in_array($this->selectedCity, array_column($this->cityPrices, 'city'))) {
            return;
        }

        $this->cityPrices[] = [
            'city' => $this->selectedCity,
            'refill_price' => '',
            'full_price' => '',
            'id' => uniqid(),
        ];

        $this->selectedCity = '';
    }

    public function removeCityPrice($index)
    {
        unset($this->cityPrices[$index]);
        $this->cityPrices = array_values($this->cityPrices);
    }

    public function save()
    {
        // This is just a placeholder for future implementation
        // In a real implementation, we would validate and save the data to the database

        $this->dispatch('bottle-type-created', [
            'message' => 'Type de bouteille créé avec succès!',
        ]);
    }

    public function render()
    {
        return view('livewire.bottle.create-bottle-type-form');
    }
}
