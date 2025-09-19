<?php

namespace App\Http\Api\Responses\Customer;

use App\Http\Api\Responses\ApiResponse;
use App\Http\Resources\Customer\CustomerDeliveryAddressResource;
use App\Models\CustomerDeliveryAddress;

class UpdateCustomerDeliveryAddressResponse extends ApiResponse
{
    public static function withAddress(CustomerDeliveryAddress $address): self
    {
        return new self(
            new CustomerDeliveryAddressResource($address),
            'Adresse de livraison mise à jour avec succès',
            true,
            200
        );
    }
}
