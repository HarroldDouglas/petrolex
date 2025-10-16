<?php

namespace App\Services;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

abstract class BaseServiceForEntity implements BaseServiceForEntityInterface
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

    public function findOrFail(int $id): Model
    {
        $model = $this->repository->find($id);
        
        if (!$model) {
            $modelClass = $this->getModel();
            throw (new \Illuminate\Database\Eloquent\ModelNotFoundException)->setModel($modelClass, [$id]);
        }
        
        return $model;
    }

    public function update(Model $model, array $data): Model
    {
        return $this->executeInTransaction(function () use ($model, $data) {
            $filteredData = array_filter($data, fn ($v) => ! is_null($v));
            $updatedModel = $this->repository->update($model, $filteredData);

            return $updatedModel;
        });
    }

    public function delete(Model $model): bool
    {
        return $this->executeInTransaction(function () use ($model) {
            return $this->repository->delete($model);
        });
    }

    public function getAll(): Collection
    {
        return $this->repository->all();
    }

    public function paginate(int $perPage = 15): mixed
    {
        return $this->repository->paginate($perPage);
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
