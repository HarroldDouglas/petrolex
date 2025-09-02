<?php

namespace App\Http\Api\Responses\Geography;

use App\Http\Api\Responses\ApiResponse;
use App\Http\Resources\Geography\CountryResource;
use Illuminate\Database\Eloquent\Collection;

class CountryListResponse extends ApiResponse
{
    public static function withCountries(Collection $countries): self
    {
        return new self(
            CountryResource::collection($countries),
            'Liste des pays récupérée avec succès'
        );
    }
}
