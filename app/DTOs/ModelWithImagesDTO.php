<?php

namespace App\DTOs;

use Illuminate\Database\Eloquent\Model;

class ModelWithImagesDTO
{
    public function __construct(
        public readonly Model $model,
        public readonly ImageDataDTO $mainImage,
        /**
         * @var ImageDataDTO[]
         */
        public readonly array $images,
    ) {}

    public static function fromModel(Model $model): self
    {
        $mainImage = ImageDataDTO::fromMedia($model->main_image ?? null);

        $images = $model->images?->map(fn ($media) => ImageDataDTO::fromMedia($media))->toArray() ?? [];

        return new self($model, $mainImage, $images);
    }

    public function toArray(): array
    {
        return [
            'model' => $this->model->toArray(),
            'main_image' => $this->mainImage->toArray(),
            'images' => array_map(fn (ImageDataDTO $dto) => $dto->toArray(), $this->images),
        ];
    }
}
