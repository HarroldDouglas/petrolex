<?php

namespace App\Services;

use App\DTOs\ModelWithImagesDTO;
use App\Models\BaseModelWithMedia;
use App\Repositories\Contracts\BaseRepositoryInterface;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

abstract class BaseService
{
    public function __construct(
        protected BaseRepositoryInterface $repository
    ) {}

    public function create(array $data): Model
    {
        try {
            DB::beginTransaction();

            $modelData = $this->filterDataForModel($data);
            $model = $this->repository->create($modelData);

            $this->processImages($model, $data);
            $this->afterCreate($model, $data);

            DB::commit();

            return $model;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("{$this->getModelName()} creation failed: ".$e->getMessage());
            throw $e;
        }
    }

    public function find(int $id): ?Model
    {
        return $this->repository->find($id)?->load('media');
    }

    public function getByIdWithImageData(int $id): ?ModelWithImagesDTO
    {
        $model = $this->find($id);

        if (! $model) {
            return null;
        }

        return ModelWithImagesDTO::fromModel($model);
    }

    public function getAllImagesForModel(int $id): ?array
    {
        $model = $this->find($id);

        if (! $model || ! ($model instanceof BaseModelWithMedia)) {
            return null;
        }

        return $model->getAllImages()
            ->map(fn ($media) => [
                'id' => $media->id,
                'name' => $media->name,
                'collection' => $media->collection_name,
                'url' => $media->getUrl(),
                'thumb_url' => $media->getUrl('thumb'),
            ])->toArray();
    }

    public function update(Model $model, array $data): Model
    {
        try {
            DB::beginTransaction();

            $modelData = $this->filterDataForModel($data);
            $model->update($modelData);

            $this->processImages($model, $data);
            $this->afterUpdate($model, $data);

            DB::commit();

            return $model;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("{$this->getModelName()} update failed: ".$e->getMessage());
            throw $e;
        }
    }

    protected function processImages(Model $model, array $data): void
    {
        if (! ($model instanceof BaseModelWithMedia)) {
            return;
        }

        if ($model->requiresMainImage()) {
            $this->handleMainImageStrategy($model, $data);
        } else {
            $this->handleMultipleImagesOnlyStrategy($model, $data);
        }
    }

    private function handleMainImageStrategy(BaseModelWithMedia $model, array $data): void
    {
        $this->handleMainImage($model, $data);
        $this->handleAdditionalImages($model, $data);
    }

    private function handleMultipleImagesOnlyStrategy(BaseModelWithMedia $model, array $data): void
    {
        if (isset($data['images']) && is_array($data['images'])) {
            $validImages = $this->filterValidImages($data['images']);

            if (! empty($validImages)) {
                try {
                    // On utilise DB::transaction pour s'assurer que toutes les images sont ajoutées ou aucune
                    DB::transaction(function () use ($model, $validImages) {
                        $addedMedia = $model->addMultipleImages($validImages);

                        Log::info('Added multiple images to model', [
                            'model_type' => get_class($model),
                            'model_id' => $model->id,
                            'images_count' => count($validImages),
                            'media_ids' => collect($addedMedia)->pluck('id')->toArray(),
                        ]);

                        // Vérification que les images sont bien présentes en base
                        $mediaCount = DB::table('media')
                            ->where('model_type', get_class($model))
                            ->where('model_id', $model->id)
                            ->where('collection_name', 'images')
                            ->count();

                        if ($mediaCount != count($validImages)) {
                            Log::warning('Mismatch between added images and media records', [
                                'expected' => count($validImages),
                                'actual' => $mediaCount,
                            ]);
                        }
                    });

                } catch (\Exception $e) {
                    Log::error('Failed to add images: '.$e->getMessage(), [
                        'model_type' => get_class($model),
                        'model_id' => $model->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    throw $e; // Propager l'erreur pour déclencher le rollback global
                }
            }
        }
    }

    private function handleMainImage(BaseModelWithMedia $model, array $data): void
    {
        if (! isset($data['main_image'])) {
            return;
        }

        $mainImage = $data['main_image'];
        if ($mainImage instanceof UploadedFile && $this->isValidImage($mainImage)) {
            try {
                $model->setMainImage(
                    $mainImage,
                    $this->getImageName($model, 'main')
                );
            } catch (\Exception $e) {
                Log::error('Failed to set main image: '.$e->getMessage());
            }
        }
    }

    private function handleAdditionalImages(BaseModelWithMedia $model, array $data): void
    {
        if (! isset($data['images']) || ! is_array($data['images'])) {
            return;
        }

        $validImages = $this->filterValidImages($data['images']);

        if (! empty($validImages)) {
            try {
                $model->addMultipleImages($validImages);
            } catch (\Exception $e) {
                Log::error('Failed to add additional images: '.$e->getMessage());
            }
        }
    }

    private function filterValidImages(array $images): array
    {
        return array_filter($images, fn ($file) => $this->isValidImage($file));
    }

    private function isValidImage($file): bool
    {
        return $file instanceof UploadedFile &&
               $file->isValid() &&
               file_exists($file->getRealPath());
    }

    protected function filterDataForModel(array $data): array
    {
        return collect($data)
            ->except($this->getImageFields())
            ->toArray();
    }

    protected function getImageFields(): array
    {
        return ['main_image', 'images'];
    }

    protected function getImageName(BaseModelWithMedia $model, string $type = ''): string
    {
        $modelName = $this->getModelName();
        $identifier = $model->getImageIdentifier();

        return "{$modelName} {$identifier}".($type ? " - {$type}" : '');
    }

    protected function getModelName(): string
    {
        return class_basename($this->getModel());
    }

    abstract protected function getModel(): string;

    protected function afterCreate(Model $model, array $data): void {}

    protected function afterUpdate(Model $model, array $data): void {}

    protected function beforeDelete(Model $model): void {}
}
