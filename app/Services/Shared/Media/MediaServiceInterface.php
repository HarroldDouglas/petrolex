<?php

namespace App\Services\Shared\Media;

use App\DTOs\ModelWithImagesDTO;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;

interface MediaServiceInterface
{
    public function attachMedia(Model $model, UploadedFile|array $files, string $collection = 'default'): array;

    public function detachMedia(Model $model, int $mediaId): bool;

    public function replaceMedia(Model $model, UploadedFile|array $files, string $collection = 'default'): array;

    public function getModelMediaData(Model $model): ?ModelWithImagesDTO;

    public function getAllImagesForModel(Model $model): array;

    public function clearMediaCollection(Model $model, string $collection): bool;

    // Mehods for handling different image strategies
    public function handleMainWithMultipleStrategy(Model $model, ?UploadedFile $mainImage, array $images): void;

    public function handleMainImageStrategy(Model $model, UploadedFile $mainImage): void;

    public function handleMultipleImagesOnlyStrategy(Model $model, array $data): void;

    public function handleSingleImageStrategy(Model $model, UploadedFile $image): void;
}
