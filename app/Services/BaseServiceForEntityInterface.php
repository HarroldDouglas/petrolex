<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface BaseServiceForEntityInterface
{
    public function create(array $data): Model;

    public function find(int $id): ?Model;

    public function update(Model $model, array $data): Model;

    public function delete(Model $model): bool;

    public function getAll(): Collection;

    public function paginate(int $perPage = 15): mixed;
}
