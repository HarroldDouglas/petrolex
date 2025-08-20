<?php

declare(strict_types=1);

namespace App\Http\Api\Resources\Order;

use App\Models\OrderPayment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OrderPayment
 *
 * @property int $id
 * @property string $payment_status
 * @property string $payment_date
 * @property string $payment_reference
 * @property string $payment_method
 */
class OrderPaymentResource extends JsonResource
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
            'status' => $this->payment_status,
            'date' => $this->payment_date,
            'reference' => $this->payment_reference,
            'method' => $this->payment_method,
        ];
    }
}
