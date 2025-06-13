<?php

namespace App\Livewire\Accessory;

use App\DTOs\Accessory\AccessoryTypeDTO;
use App\Http\Requests\StoreAccessoryTypeRequest;
use App\Models\AccessoryType;
use Illuminate\Foundation\Http\FormRequest;

class CreateAccessoryType extends AbstractAccessoryTypeForm
{
    protected function customRequest(): FormRequest
    {
        return new StoreAccessoryTypeRequest;
    }

    public function submit()
    {
        $validatedData = $this->validate();

        try {
            $dto = AccessoryTypeDTO::fromArray($validatedData);
            /** @var AccessoryType */
            $accessoryType = $this->accessoryTypeService->createWithMedia($dto->toArray());

            session()->flash('success', 'Type d\'accessoire créé avec succès.');

            return redirect()->route('accessories.edit', $accessoryType->id);
        } catch (\Exception $e) {
            session()->flash('error', 'Une erreur est survenue lors de la création: '.$e->getMessage());

            return null;
        }
    }
}
