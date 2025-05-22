<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\baseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class BaseEloquentRepository implements baseRepositoryInterface
{
    /**
     * The Eloquent model instance
     */
    protected Model $model;

    /**
     * {@inheritDoc}
     */
    public function create(array $attributes): Model
    {
        return $this->model->create($attributes);
    }

    /**
     * {@inheritDoc}
     */
    public function find($id): ?Model
    {
        return $this->model->find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function update(Model $model, array $attributes): bool
    {
        return $model->update($attributes);
    }

    /**
     * {@inheritDoc}
     */
    public function delete(Model $model): bool
    {
        return $model->delete();
    }

    /**
     * {@inheritDoc}
     */
    public function all(array $columns = ['*']): Collection
    {
        return $this->model->all($columns);
    }
}
