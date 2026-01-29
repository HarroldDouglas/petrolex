<?php

namespace App\DTOs\Accessory;

use App\DTOs\BaseDTO;
use Illuminate\Http\UploadedFile;

class AccessoryTypeDTO extends BaseDTO
{
    /**
     * @param  UploadedFile[]|null  $images
     */
    public function __construct(
        public readonly string $name,
        public readonly float $price,
        public readonly string $description,
        public readonly ?string $name_en = null,
        public readonly ?string $description_en = null,
        public readonly bool $is_active = true,
        public readonly ?array $images = null,
    ) {}

    /**
     * Create a DTO from request data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            price: (float) $data['price'],
            description: $data['description'],
            name_en: $data['name_en'] ?? null,
            description_en: $data['description_en'] ?? null,
            is_active: $data['is_active'] ?? true,
            images: $data['images'] ?? null,
        );
    }
}
