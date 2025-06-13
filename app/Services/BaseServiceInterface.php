<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface BaseServiceInterface
{
    public function create(array $data): Model;

    public function find(int $id): ?Model;

    public function update(Model $model, array $data): Model;

    public function delete(Model $model): bool;

    public function getAll(array $filters = [], array $with = []): Collection;

    public function paginate(int $perPage = 15, array $filters = [], array $with = []): mixed;
}
