<?php

namespace App\Http\Api\Responses\Customer;

use App\Http\Api\Resources\CustomerResource;
use App\Http\Api\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Support\Collection;

class CustomerResponse extends ApiResponse
{
    /**
     * Return response with multiple customers.
     *
     * @param  Collection<int, User>|array<int, User>  $customers
     */
    public static function withCollection(
        Collection|array $customers,
        ?string $message = null,
        int $statusCode = 200
    ): self {
        return new self(
            CustomerResource::collection($customers),
            $message ?? 'Clients récupérés avec succès',
            true,
            $statusCode
        );
    }
}
