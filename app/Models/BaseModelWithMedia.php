<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
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
                    ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
            } elseif ($collectionName === 'images') {
                $this->addMediaCollection('images')
                    ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
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
