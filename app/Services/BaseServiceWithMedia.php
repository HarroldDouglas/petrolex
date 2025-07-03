<?php

namespace App\Services;

use App\DTOs\ModelWithImagesDTO;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Services\Shared\Media\MediaServiceInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\HasMedia;

abstract class BaseServiceWithMedia extends BaseServiceForEntity implements HasMediaServiceInterface
{
    public function __construct(
        protected BaseRepositoryInterface $baseRepository,
        protected MediaServiceInterface $mediaService
    ) {
        parent::__construct($baseRepository);
    }

    public function createWithMedia(array $data): Model
    {
        return $this->executeInTransaction(function () use ($data) {
            $modelData = $this->filterDataForModel($data);
            /** @var HasMedia|Model $model */
            $model = $this->repository->create($modelData);

            $this->processMediaWithStrategy($model, $data);

            return $model->load('media');
        });
    }

    /**
     * Update a model with media handling
     *
     * @param HasMedia|Model $model The model to update
     * @param array $data The data to update the model with
     * @param array<int>|null $imagesIdsToDelete Optional array of media IDs to delete
     * @return Model The updated model
     */
    public function updateWithMedia(HasMedia|Model $model, array $data, ?array $imagesIdsToDelete = null): Model
    {
        return $this->executeInTransaction(function () use ($model, $data, $imagesIdsToDelete) {
            $modelData = $this->filterDataForModel($data);
            /** @var HasMedia|Model $updatedModel */
            $updatedModel = $this->repository->update($model, $modelData);

            if (!empty($imagesIdsToDelete)) {
                foreach ($imagesIdsToDelete as $mediaId) {
                    $this->mediaService->detachMedia($updatedModel, (int) $mediaId);
                }
            }

            $this->processMediaWithStrategy($updatedModel, $data);

            return $updatedModel->load('media');
        });
    }

    /**
     * Remove a media item from a model
     */
    public function removeMedia(HasMedia $model, int $mediaId): bool
    {
        return $this->mediaService->detachMedia($model, $mediaId);
    }

    public function getWithMediaData(int $id): ?ModelWithImagesDTO
    {
        /** @var HasMedia $model */
        $model = $this->find($id);

        if (! $model) {
            return null;
        }

        return $this->mediaService->getModelMediaData($model);
    }

    protected function processMediaWithStrategy(HasMedia $model, array $data): void
    {
        $strategy = $this->getMediaStrategy();
        match ($strategy) {
            'main_only' => $this->mediaService->handleMainImageStrategy(
                $model,
                $this->extractMainImage($data)
            ),
            'multiple_only' => $this->mediaService->handleMultipleImagesOnlyStrategy(
                $model,
                $this->extractImages($data)
            ),
            'single' => $this->mediaService->handleSingleImageStrategy(
                $model,
                $this->extractSingleImageFromData($data)
            ),
            'main_with_multiple' => $this->mediaService->handleMainWithMultipleStrategy(
                $model,
                $this->extractMainImage($data),
                $this->extractImages($data)
            ),
            default => null
        };
    }

    private function extractSingleImageFromData(array $data): ?UploadedFile
    {
        foreach ($this->getMediaFields() as $field) {
            if (isset($data[$field]) && $data[$field] instanceof UploadedFile) {
                return $data[$field];
            }
        }

        return null;
    }

    private function extractMainImage(array $data): ?UploadedFile
    {
        return isset($data['main_image']) && $data['main_image'] instanceof UploadedFile
            ? $data['main_image']
            : null;
    }

    private function extractImages(array $data): array
    {
        return isset($data['images']) && is_array($data['images'])
            ? $data['images']
            : [];
    }

    protected function filterDataForModel(array $data): array
    {
        return collect($data)
            ->except($this->getMediaFields())
            ->toArray();
    }

    protected function getMediaStrategy(): string
    {
        $model = new ($this->getModel());

        if ($model->requiresMainImage() && $model->supportsMultipleImages()) {
            return 'main_with_multiple';
        }

        if ($model->requiresMainImage() && ! $model->supportsMultipleImages()) {
            return 'main_only';
        }

        if (! $model->requiresMainImage() && $model->supportsMultipleImages()) {
            return 'multiple_only';
        }

        return 'single';
    }

    /**
     * should retourn just images fields to not consider when filtering data for model
     *
     * @return string[]
     */
    abstract protected function getMediaFields(): array;
}
