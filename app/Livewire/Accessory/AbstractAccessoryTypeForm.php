<?php

namespace App\Livewire\Accessory;

use App\Services\Accessory\AccessoryTypeService;
use App\Services\Shared\Media\MediaServiceInterface;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Livewire\Component;
use Livewire\WithFileUploads;

abstract class AbstractAccessoryTypeForm extends Component
{
    use WithFileUploads;

    public $name = '';
    public $price = '';
    public $description = '';
    public $is_active = true;
    /** @var UploadedFile[] */
    public array $images = [];

    /** @var AccessoryTypeService */
    protected $accessoryTypeService;

    /** @var MediaServiceInterface */
    protected $mediaService;

    public function boot(
        AccessoryTypeService $accessoryTypeService,
        MediaServiceInterface $mediaService,
    ) {
        $this->accessoryTypeService = $accessoryTypeService;
        $this->mediaService = $mediaService;
    }

    public function rules()
    {
        $request = $this->customRequest();

        return method_exists($request, 'rules') ? $request->rules() : [];
    }

    public function messages()
    {
        $request = $this->customRequest();

        return method_exists($request, 'messages') ? $request->messages() : [];
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
