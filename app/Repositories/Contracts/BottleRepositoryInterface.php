<?php

namespace App\Repositories\Contracts;

use App\Enums\BottleStatus;
use Illuminate\Database\Eloquent\Collection;

interface BottleRepositoryInterface extends baseRepositoryInterface
{
    public function getBottleHistory($bottleId): Collection;

    public function updateStatus($bottleId, BottleStatus $status): void;
}
