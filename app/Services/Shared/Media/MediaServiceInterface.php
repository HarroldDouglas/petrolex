<?php

namespace App\Services\Shared\Media;

use App\DTOs\ImageDataDTO;
use App\DTOs\ModelWithImagesDTO;
use App\Models\BaseModelWithMedia;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

interface MediaServiceInterface
{
    /**
     * Attach media to a model
     *
     * @param  UploadedFile|UploadedFile[]  $files
     * @return array<Media>
     */
    public function attachMedia(BaseModelWithMedia|User $model, UploadedFile|array $files, string $collection = 'images'): array;

    /**
     * Detach media from a model
     */
    public function detachMedia(BaseModelWithMedia|User $model, int $mediaId): bool;

    /**
     * Replace all media in a collection
     *
     * @param  UploadedFile|UploadedFile[]  $files
     * @return array<Media>
     */
    public function replaceMedia(BaseModelWithMedia|User $model, UploadedFile|array $files, string $collection = 'images'): array;

    /**
     * Get media data for a model
     */
    public function getModelMediaData(BaseModelWithMedia|User $model): ?ModelWithImagesDTO;

    /**
     * Get all images for a model
     *
     * @return array<ImageDataDTO>
     */
    public function getAllImagesForModel(BaseModelWithMedia|User $model): array;

    /**
     * Clear a media collection for a model
     */
    public function clearMediaCollection(BaseModelWithMedia|User $model, string $collection): bool;

    /**
     * Handle media strategy for models with main image and multiple images
     *
     * @param  UploadedFile[]  $images
     */
    public function handleMainWithMultipleStrategy(BaseModelWithMedia|User $model, ?UploadedFile $mainImage, array $images): void;

    /**
     * Handle media strategy for models with main image only
     */
    public function handleMainImageStrategy(BaseModelWithMedia|User $model, UploadedFile $mainImage): void;

    /**
     * Handle media strategy for models with multiple images only
     *
     * @param  UploadedFile[]  $images
     */
    public function handleMultipleImagesOnlyStrategy(BaseModelWithMedia|User $model, array $images): void;

    /**
     * Handle media strategy for models with a single image
     */
    public function handleSingleImageStrategy(BaseModelWithMedia|User $model, UploadedFile $image): void;
}
