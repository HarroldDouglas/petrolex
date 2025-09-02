<?php

namespace App\Http\Resources\Geography;

use App\Models\Geography\Country;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Country */
class CountryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'phone_code' => $this->phone_code,
            'currency' => $this->currency,
            'is_active' => $this->is_active,
        ];
    }
}
