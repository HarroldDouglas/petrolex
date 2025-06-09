<?php

namespace App\Livewire\Accessory;

use App\Services\Accessory\AccessoryTypeService;
use Illuminate\Foundation\Http\FormRequest;
use Livewire\Component;
use Livewire\WithFileUploads;

abstract class AbstractAccessoryTypeForm extends Component
{
    use WithFileUploads;

    public $name = '';
    public $price = '';
    public $description = '';
    public $is_active = true;
    public $images = [];

    /** @var AccessoryTypeService */
    protected $accessoryTypeService;

    public function boot(AccessoryTypeService $accessoryTypeService)
    {
        $this->accessoryTypeService = $accessoryTypeService;
    }

    public function rules()
    {
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
        return view('livewire.accessories.accessory-type-form');
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
