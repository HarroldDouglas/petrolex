<?php

declare(strict_types=1);

namespace App\Http\Api\Requests\TrackingDelivery;

use Illuminate\Foundation\Http\FormRequest;

abstract class AbstractDeliveryTrackingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
}
