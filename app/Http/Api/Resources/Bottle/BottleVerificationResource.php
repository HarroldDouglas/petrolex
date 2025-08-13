<?php

declare(strict_types=1);

namespace App\Http\Api\Resources\Bottle;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class BottleVerificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'authentic' => $this['authentic'],
            'status' => $this['status'],
        ];
    }
}
