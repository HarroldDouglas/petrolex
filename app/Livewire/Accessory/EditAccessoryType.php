<?php

namespace App\Livewire\Accessory;

use App\Http\Requests\UpdateAccessoryTypeRequest;
use App\Models\AccessoryType;
use Illuminate\Foundation\Http\FormRequest;

class EditAccessoryType extends AbstractAccessoryTypeForm
{
    public $accessoryType;
    public $existingImages = [];

    public function mount(AccessoryType $accessoryType)
    {
        $this->accessoryType = $accessoryType;
        $this->name = $accessoryType->name;
        $this->price = $accessoryType->price;
        $this->description = $accessoryType->description;
        $this->is_active = $accessoryType->is_active;

        $this->existingImages = $this->accessoryTypeService->getAllImagesForModel($accessoryType->id) ?? [];
    }

    protected function customRequest(): FormRequest
    {
        return new UpdateAccessoryTypeRequest($this->accessoryType->id);
    }

    public function submit()
    {
        $validatedData = $this->validate();

        if (! empty($this->images)) {
            $validatedData['images'] = $this->images;
        }

        $this->accessoryTypeService->update($this->accessoryType, $validatedData);

        session()->flash('success', 'Type d\'accessoire mis à jour avec succès.');

        return redirect()->route('accessories.index');
    }

    public function removeImage($imageId)
    {
        $media = $this->accessoryType->media->where('id', $imageId)->first();

        if ($media) {
            $media->delete();
            $this->existingImages = $this->accessoryTypeService->getAllImagesForModel($this->accessoryType->id) ?? [];
            session()->flash('success', 'Image supprimée avec succès.');
        }
    }
}
