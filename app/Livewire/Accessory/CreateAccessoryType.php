<?php

namespace App\Livewire\Accessory;

use App\Http\Requests\StoreAccessoryTypeRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            if (! empty($this->images)) {
                // Filtrer les images invalides
                $validImages = [];
                foreach ($this->images as $image) {
                    if ($image->isValid() && file_exists($image->getRealPath())) {
                        $validImages[] = $image;
                    }
                }

                if (count($validImages) > 0) {
                    $validatedData['images'] = $validImages;
                }
            }

            $accessoryType = $this->accessoryTypeService->create($validatedData);

            // Vérification du nombre d'images réellement ajoutées
            $mediaCount = DB::table('media')
                ->where('model_type', get_class($accessoryType))
                ->where('model_id', $accessoryType->id)
                ->where('collection_name', 'images')
                ->count();

            Log::info('Direct DB check:', [
                'db_media_count' => $mediaCount,
                'model_media_count' => $accessoryType->getMedia('images')->count(),
                'fresh_media_count' => $accessoryType->fresh()->getMedia('images')->count(),
            ]);

            // Rechargement complet du modèle avec ses relations
            $accessoryType = $accessoryType->fresh()->load('media');

            session()->flash('success', 'Type d\'accessoire créé avec succès.');

            return redirect()->route('accessories.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Une erreur est survenue lors de la création: '.$e->getMessage());

            return null;
        }
    }
}
