<?php

namespace App\Services;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

abstract class BaseService implements BaseServiceInterface
{
    public function __construct(
        protected BaseRepositoryInterface $repository
    ) {}

    public function create(array $data): Model
    {
        return $this->executeInTransaction(function () use ($data) {
            $model = $this->repository->create($data);

            return $model;
        });
    }

    public function find(int $id): ?Model
    {
        return $this->repository->find($id);
    }

    public function update(Model $model, array $data): Model
    {
        return $this->executeInTransaction(function () use ($model, $data) {
            $updatedModel = $this->repository->update($model, $data);

            return $updatedModel;
        });
    }

    public function delete(Model $model): bool
    {
        return $this->executeInTransaction(function () use ($model) {
            return $this->repository->delete($model);
        });
    }

    public function getAll(array $filters = [], array $with = []): Collection
    {
        return $this->repository->all($filters, $with);
    }

    public function paginate(int $perPage = 15, array $filters = [], array $with = []): mixed
    {
        return $this->repository->paginate($perPage, $filters, $with);
    }

    protected function executeInTransaction(callable $callback): mixed
    {
        try {
            DB::beginTransaction();

            $result = $callback();

            DB::commit();

            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("{$this->getModelName()} operation failed: ".$e->getMessage());
            throw $e;
        }
    }

    protected function getModelName(): string
    {
        return class_basename($this->getModel());
    }

    abstract protected function getModel(): string;
}
