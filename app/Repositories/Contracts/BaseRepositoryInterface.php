<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface BaseRepositoryInterface
{
    /**
     * Create a new instance
     */
    public function create(array $attributes): Model;

    /**
     * Find model by id
     *
     * @param  int|string  $id
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function find($id): ?Model;

    /**
     * Update a model
     */
    public function update(Model $model, array $attributes): Model;

    /**
     * Delete a model
     */
    public function delete(Model $model): bool;

    /**
     * Get all records
     */
    public function all(array $columns = ['*']): Collection;
}
