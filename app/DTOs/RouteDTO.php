<?php

declare(strict_types=1);

namespace App\DTOs;

final class RouteDTO
{
    /**
     * @param  array<string, mixed>|null  $geometry
     */
    public function __construct(
        public readonly int $duration,
        public readonly float $distance,
        public readonly ?array $geometry
    ) {}
}
