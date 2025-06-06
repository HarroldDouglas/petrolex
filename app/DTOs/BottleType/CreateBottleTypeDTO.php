<?php

namespace App\DTOs\BottleType;

class CreateBottleTypeDTO
{
    public function __construct(
        public string $name,
        public string $capacity,
        public float $content_price,
        public float $bottle_with_content_price,
        public bool $is_active,
        public ?string $description,
        public ?float $height,
        public ?float $width,
        public ?float $radius,
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'capacity' => $this->capacity,
            'content_price' => $this->content_price,
            'bottle_with_content_price' => $this->bottle_with_content_price,
            'is_active' => $this->is_active,
            'description' => $this->description,
            'height' => $this->height,
            'width' => $this->width,
            'radius' => $this->radius,
        ];
    }
}
