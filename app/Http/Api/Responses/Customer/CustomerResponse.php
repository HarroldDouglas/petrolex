<?php

namespace App\Http\Api\Responses\Customer;

use App\Http\Api\Resources\CustomerResource;
use App\Http\Api\Responses\ApiResponse;
use App\Models\Customer;
use Illuminate\Support\Collection;

class CustomerResponse extends ApiResponse
{
    /**
     * Return response with multiple customers.
     *
     * @param  Collection<int,Customer>|array<int, \App\Models\Customer>  $customers
     */
    public static function many(
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

    /**
     * Return response with a single customer.
     */
    public static function single(
        Customer $customer,
        ?string $message = null,
        int $statusCode = 200
    ): self {
        return new self(
            new CustomerResource($customer),
            $message ?? 'Client récupéré avec succès',
            true,
            $statusCode
        );
    }
}
