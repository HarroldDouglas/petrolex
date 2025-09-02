<?php

namespace App\Http\Api\Controllers\Geography;

use App\Http\Api\Responses\Geography\CountryListResponse;
use App\Http\Controllers\Controller;
use App\Models\Geography\Country;

class CountryController extends Controller
{
    /**
     * Get all active countries.
     *
     * Route: GET /api/geography/countries
     * Name: api.geography.countries.index
     */
    public function index(): CountryListResponse
    {
        $countries = Country::where('is_active', true)
            ->orderBy('name')
            ->get();

        return CountryListResponse::withCountries($countries);
    }
}
