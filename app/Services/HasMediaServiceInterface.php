<?php

namespace App\Services;

use App\DTOs\ModelWithImagesDTO;
use Illuminate\Database\Eloquent\Model;

interface HasMediaServiceInterface extends BaseServiceForEntityInterface
{
    public function createWithMedia(array $data): Model;

    public function updateWithMedia(Model $model, array $data): Model;

    public function getWithMediaData(int $id): ?ModelWithImagesDTO;
}
