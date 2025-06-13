<?php

namespace App\Livewire\Accessory;

use App\DTOs\Accessory\AccessoryTypeDTO;
use App\Http\Requests\UpdateAccessoryTypeRequest;
use App\Models\AccessoryType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

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
            // Rechargement complet du modèle pour s'assurer que les relations sont bien actualisées
            $this->accessoryType = AccessoryType::with('media')->find($this->accessoryType->id);

            // Réinitialisation du tableau d'images à partir du modèle rechargé
            $this->existingImages = $this->mediaService->getAllImagesForModel($this->accessoryType) ?? [];

            Log::debug('Image removed successfully', [
                'image_id' => $imageId,
                'accessory_type_id' => $this->accessoryType->id,
                'existing images' => count($this->existingImages),
            ]);

            // Notification de succès
            $this->dispatch('image-removed', imageId: $imageId);
            session()->flash('success', 'Image supprimée avec succès.');
        } else {
            session()->flash('error', 'Impossible de supprimer l\'image.');
        }
    }
}
