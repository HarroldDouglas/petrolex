<?php

namespace App\Services\Shared\Media;

use App\DTOs\ImageDataDTO;
use App\DTOs\ModelWithImagesDTO;
use App\Models\BaseModelWithMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class MediaService implements MediaServiceInterface
{
    /**
     * @var UploadedFile|UploadedFile[]
     *
     * @return array<\Spatie\MediaLibrary\MediaCollections\Models\Media>
     */
    public function attachMedia(Model $model, UploadedFile|array $files, string $collection = 'images'): array
    {
        if (! $this->supportsMedia($model)) {
            throw new InvalidArgumentException('Model does not support media');
        }

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
                    'model_id' => $model->id,
                    'collection' => $collection,
                ]);
            }
        }

        return $addedMedia;
    }

    public function detachMedia(Model $model, int $mediaId): bool
    {
        if (! $this->supportsMedia($model)) {
            return false;
        }

        try {
            $media = $model->getMedia('images')->find($mediaId);

            if ($media) {
                $media->delete();

                return true;
            }
        } catch (\Exception $e) {
            Log::error("Failed to detach media: {$e->getMessage()}", [
                'model_type' => get_class($model),
                'model_id' => $model->id,
                'media_id' => $mediaId,
            ]);
        }

        return false;
    }

    /**
     * @var UploadedFile|UploadedFile[]
     *
     * @return array<\Spatie\MediaLibrary\MediaCollections\Models\Media>
     *
     * @throws InvalidArgumentException
     */
    public function replaceMedia(Model $model, UploadedFile|array $files, string $collection = 'images'): array
    {
        if (! $this->supportsMedia($model)) {
            throw new InvalidArgumentException('Model does not support media');
        }

        $this->clearMediaCollection($model, $collection);

        return $this->attachMedia($model, $files, $collection);
    }

    public function getModelMediaData(Model $model): ?ModelWithImagesDTO
    {
        if (! $this->supportsMedia($model)) {
            return null;
        }

        $model->load('media');

        return ModelWithImagesDTO::fromModel($model);
    }

    /**
     * @return array<ImageDataDTO>
     */
    public function getAllImagesForModel(Model $model): array
    {
        if (! $this->supportsMedia($model)) {
            return [];
        }

        $model->load('media');

        return $model->media
            ->map(fn ($media) => ImageDataDTO::fromMedia($media))
            ->toArray();
    }

    public function clearMediaCollection(Model $model, string $collection): bool
    {
        if (! $this->supportsMedia($model)) {
            return false;
        }

        try {
            $model->clearMediaCollection($collection);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to clear media collection: {$e->getMessage()}", [
                'model_type' => get_class($model),
                'model_id' => $model->id,
                'collection' => $collection,
            ]);

            return false;
        }
    }

    /**
     * @var UploadedFile[]
     */
    public function handleMainWithMultipleStrategy(Model $model, ?UploadedFile $mainImage, array $images): void
    {
        if (! $this->supportsMedia($model)) {
            return;
        }

        if ($mainImage) {
            $this->replaceMedia($model, $mainImage, 'main_image');
        }

        if (! empty($images)) {
            $this->attachMedia($model, $images, 'images');
        }
    }

    public function handleMainImageStrategy(Model $model, UploadedFile $mainImage): void
    {
        if (! $this->supportsMedia($model)) {
            return;
        }

        $this->replaceMedia($model, $mainImage, 'main_image');
    }

    /**
     * @param  array<UploadedFile>  $images
     */
    public function handleMultipleImagesOnlyStrategy(Model $model, array $images): void
    {
        if (! $this->supportsMedia($model)) {
            return;
        }

        $this->attachMedia($model, $images, 'images');
    }

    public function handleSingleImageStrategy(Model $model, UploadedFile $image): void
    {
        if (! $this->supportsMedia($model)) {
            return;
        }

        $this->replaceMedia($model, $image, 'images');
    }

    private function supportsMedia(Model $model): bool
    {
        return $model instanceof BaseModelWithMedia;
    }
}
