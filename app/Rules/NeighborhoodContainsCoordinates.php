<?php

namespace App\Rules;

use App\Models\Geography\Neighborhood;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class NeighborhoodContainsCoordinates implements DataAwareRule, ValidationRule
{
    private array $data = [];

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $lat = isset($this->data['latitude']) ? (float) $this->data['latitude'] : null;
        $lng = isset($this->data['longitude']) ? (float) $this->data['longitude'] : null;

        // No coordinates submitted — nothing to validate
        if ($lat === null || $lng === null) {
            return;
        }

        $neighborhood = Neighborhood::find($value);

        if (! $neighborhood || ! $neighborhood->polygon) {
            // No polygon data yet — skip validation
            return;
        }

        if (! $this->pointInPolygon($lat, $lng, $neighborhood->polygon)) {
            $fail(__('validation.delivery_address.coordinates_outside_neighborhood'));
        }
    }

    /**
     * Check if a point (lat, lng) is inside a GeoJSON geometry (Polygon or MultiPolygon).
     * GeoJSON coordinates are [longitude, latitude].
     */
    private function pointInPolygon(float $lat, float $lng, array $geometry): bool
    {
        if ($geometry['type'] === 'Polygon') {
            return $this->pointInRing($lat, $lng, $geometry['coordinates'][0]);
        }

        if ($geometry['type'] === 'MultiPolygon') {
            foreach ($geometry['coordinates'] as $polygon) {
                if ($this->pointInRing($lat, $lng, $polygon[0])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Ray casting algorithm.
     * Ring is an array of [longitude, latitude] pairs (GeoJSON format).
     */
    private function pointInRing(float $lat, float $lng, array $ring): bool
    {
        $inside = false;
        $n = count($ring);

        for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
            $xi = $ring[$i][0]; // longitude
            $yi = $ring[$i][1]; // latitude
            $xj = $ring[$j][0];
            $yj = $ring[$j][1];

            $intersect = (($yi > $lat) !== ($yj > $lat))
                && ($lng < ($xj - $xi) * ($lat - $yi) / ($yj - $yi) + $xi);

            if ($intersect) {
                $inside = ! $inside;
            }
        }

        return $inside;
    }
}
