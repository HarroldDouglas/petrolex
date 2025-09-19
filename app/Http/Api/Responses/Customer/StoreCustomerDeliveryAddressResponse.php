<?php

namespace App\Http\Api\Responses\Customer;

use App\Http\Api\Responses\ApiResponse;
use App\Http\Resources\Customer\CustomerDeliveryAddressResource;
use App\Models\CustomerDeliveryAddress;

class StoreCustomerDeliveryAddressResponse extends ApiResponse
{
    public static function withAddress(CustomerDeliveryAddress $address): self
    {
        return new self(
            new CustomerDeliveryAddressResource($address),
            'Adresse de livraison créée avec succès',
            true,
            201
        );
    }
}
