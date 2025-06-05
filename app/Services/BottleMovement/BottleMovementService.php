
<?php

namespace App\Services\Product;

use App\DTOs\BottleMovement\CreateBottleMovementDTO;
use App\Models\BottleMovement;
use App\Repositories\Contracts\BottleMovementRepositoryInterface;

class BottleMovementService
{
    public function __construct(
        protected BottleMovementRepositoryInterface $repository
    ) {}
    public function create(CreateBottleMovementDTO $data): BottleMovement{
        dd($data->toArray());
        return $this->repository->create($data->toArray());
    }
}
