<?php

namespace App\Livewire\Accessory;

use App\DTOs\Accessory\AccessoryTypeDTO;
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
        $this->name_en = $accessoryType->name_en;
        $this->price = $accessoryType->price;
        $this->description = $accessoryType->description;
        $this->description_en = $accessoryType->description_en;
        $this->is_active = $accessoryType->is_active;

        $this->existingImages = $this->mediaService->getAllImagesForModel($accessoryType) ?? [];
    }

    protected function customRequest(): FormRequest
    {
        return new UpdateAccessoryTypeRequest($this->accessoryType->id);
    }

    public function submit()
    {
        $validatedData = $this->validate();

        try {
            $dto = AccessoryTypeDTO::fromArray($validatedData);
            $this->accessoryTypeService->updateWithMedia($this->accessoryType, $dto->toArray());
            session()->flash('success', 'Type d\'accessoire mis à jour avec succès.');

            return redirect()->route('accessories.edit', $this->accessoryType->id);
        } catch (\Exception $e) {
            session()->flash('error', 'Une erreur est survenue lors de la mise à jour : '.$e->getMessage());

            return null;
        }
    }

    public function removeImage($imageId)
    {
        $removed = $this->accessoryTypeService->removeMedia($this->accessoryType, $imageId);

        if ($removed) {
            $this->existingImages = $this->mediaService->getAllImagesForModel($this->accessoryType) ?? [];

            session()->flash('success', 'Image supprimée avec succès.');
        } else {
            session()->flash('error', 'Impossible de supprimer l\'image.');
        }
    }
}
