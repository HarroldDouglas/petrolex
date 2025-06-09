<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

abstract class BaseModelWithMedia extends Model implements HasMedia
{
    use InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        $collections = $this->getImageCollections();

        foreach ($collections as $collectionName) {
            if ($collectionName === 'main_image') {
                $this->addMediaCollection('main_image')
                    ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->singleFile(true);
            } elseif ($collectionName === 'images') {
                $this->addMediaCollection('images')
                    ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->singleFile(false);
            }
        }
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(150)
            ->height(150)
            ->optimize()
            ->performOnCollections(...$this->getImageCollections());

        $this->addMediaConversion('medium')
            ->width(500)
            ->height(500)
            ->optimize()
            ->performOnCollections(...$this->getImageCollections());

        $this->addMediaConversion('large')
            ->width(1200)
            ->height(1200)
            ->optimize()
            ->performOnCollections(...$this->getImageCollections());
    }

    public function setMainImage(UploadedFile $file, ?string $name = null): ?Media
    {
        if (! $this->requiresMainImage()) {
            throw new \Exception('This model does not support main images');
        }

        return $this->addMedia($file)
            ->usingName($name ?? 'Main Image')
            ->toMediaCollection('main_image');
    }

    public function addMultipleImages(array $files): array
    {
        return DB::transaction(function () use ($files) {
            Log::info('Starting addMultipleImages in transaction', [
                'files_count' => count($files),
                'model_id' => $this->id,
            ]);

            $results = [];

            foreach ($files as $index => $file) {
                if ($file instanceof UploadedFile) {
                    try {
                        $media = $this->addMedia($file)
                            ->usingName($this->getImageIdentifier().' - Image '.($index + 1))
                            ->preservingOriginal()  // Préserver le fichier original pour éviter des problèmes
                            ->toMediaCollection('images');

                        $results[] = $media;

                        Log::info("Successfully added image {$index}", [
                            'media_id' => $media->id,
                            'media_name' => $media->name,
                        ]);

                    } catch (\Exception $e) {
                        Log::error("Failed to add image {$index}", [
                            'error' => $e->getMessage(),
                            'file_name' => $file->getClientOriginalName(),
                        ]);
                        throw $e; // Force rollback de la transaction
                    }
                }
            }

            // Assurez-vous que les modifications sont persistées
            DB::commit();

            // Vérification double après l'ajout
            $mediaCount = DB::table('media')
                ->where('model_type', get_class($this))
                ->where('model_id', $this->id)
                ->where('collection_name', 'images')
                ->count();

            Log::info('Completed addMultipleImages transaction', [
                'total_processed' => count($results),
                'media_count_in_db' => $mediaCount,
            ]);

            return $results;
        });
    }

    public function addSingleImage(UploadedFile $file, ?string $name = null): ?Media
    {
        return $this->addMedia($file)
            ->usingName($name ?? ($this->getImageIdentifier().' - Image'))
            ->toMediaCollection('images');
    }

    public function getAllImages(): \Illuminate\Support\Collection
    {
        $images = $this->getMedia('images');

        if ($this->requiresMainImage() && $this->getFirstMedia('main_image')) {
            $images = $images->prepend($this->getFirstMedia('main_image'));
        }

        return $images;
    }

    public function getMainImage(): ?Media
    {
        if ($this->requiresMainImage()) {
            return $this->getFirstMedia('main_image');
        }

        return $this->getFirstMedia('images');
    }

    public function getMainImageAttribute(): ?Media
    {
        return $this->getMainImage();
    }

    public function getImagesAttribute()
    {
        return $this->getMedia('images');
    }

    public function getImageCollections(): array
    {
        return ['images'];
    }

    abstract public function requiresMainImage(): bool;

    abstract public function supportsMultipleImages(): bool;

    abstract public function getImageIdentifier(): string;
}
