<?php

namespace App\Services\Shared\Media;

use App\DTOs\ImageDataDTO;
use App\DTOs\ModelWithImagesDTO;
use App\Models\BaseModelWithMedia;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class SpatieMediaService implements MediaServiceInterface
{
    /**
     * {@inheritDoc}
     */
    public function attachMedia(BaseModelWithMedia $model, UploadedFile|array $files, string $collection = 'images'): array
    {
        $validFiles = is_array($files) ? $files : [$files];

        if (empty($validFiles)) {
            return [];
        }

        $addedMedia = [];
        foreach ($validFiles as $file) {
            try {
                $media = $model->addMedia($file)->toMediaCollection($collection);
                $addedMedia[] = $media;
            } catch (\Exception $e) {
                Log::error("Failed to attach media: {$e->getMessage()}", [
                    'model_type' => get_class($model),
                    'model_id' => $model->getKey(),
                    'collection' => $collection,
                ]);
            }
        }

        return $addedMedia;
    }

    /**
     * {@inheritDoc}
     */
    public function detachMedia(BaseModelWithMedia $model, int $mediaId): bool
    {
        try {
            $media = $model->getMedia('images')->firstWhere('id', $mediaId);

            if ($media) {
                $media->delete();

                return true;
            }
        } catch (\Exception $e) {
            Log::error("Failed to detach media: {$e->getMessage()}", [
                'model_type' => get_class($model),
                'model_id' => $model->getKey(),
                'media_id' => $mediaId,
            ]);
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function replaceMedia(BaseModelWithMedia $model, UploadedFile|array $files, string $collection = 'images'): array
    {
        $this->clearMediaCollection($model, $collection);

        return $this->attachMedia($model, $files, $collection);
    }

    /**
     * {@inheritDoc}
     */
    public function getModelMediaData(BaseModelWithMedia $model): ?ModelWithImagesDTO
    {
        $model->load('media');

        return ModelWithImagesDTO::fromModel($model);
    }

    /**
     * {@inheritDoc}
     */
    public function getAllImagesForModel(BaseModelWithMedia $model): array
    {
        $model->load('media');

        if (method_exists($model, 'media') && $model->relationLoaded('media')) {
            return $model->getMedia('images')
                ->map(fn ($media) => ImageDataDTO::fromMedia($media))
                ->toArray();
        }

        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function clearMediaCollection(BaseModelWithMedia $model, string $collection): bool
    {
        try {
            $model->clearMediaCollection($collection);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to clear media collection: {$e->getMessage()}", [
                'model_type' => get_class($model),
                'model_id' => $model->getKey(),
                'collection' => $collection,
            ]);
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function handleMainWithMultipleStrategy(BaseModelWithMedia $model, ?UploadedFile $mainImage, array $images): void
    {
        if ($mainImage) {
            $this->replaceMedia($model, $mainImage, 'main_image');
        }

        if (! empty($images)) {
            $this->attachMedia($model, $images, 'images');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function handleMainImageStrategy(BaseModelWithMedia $model, UploadedFile $mainImage): void
    {
        $this->replaceMedia($model, $mainImage, 'main_image');
    }

    /**
     * {@inheritDoc}
     */
    public function handleMultipleImagesOnlyStrategy(BaseModelWithMedia $model, array $images): void
    {
        $this->attachMedia($model, $images, 'images');
    }

    /**
     * {@inheritDoc}
     */
    public function handleSingleImageStrategy(BaseModelWithMedia $model, UploadedFile $image): void
    {
        $this->replaceMedia($model, $image, 'images');
    }
}
