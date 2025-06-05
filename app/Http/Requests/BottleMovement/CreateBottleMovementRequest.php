<?php

namespace App\Http\Requests\BottleMovement;

use App\Enums\BottleMovementType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateBottleMovementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Adjust based on your authorization logic
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'bottle_id' => ['required', 'integer', 'exists:bottles,id'],
            'type' => ['required', Rule::enum(BottleMovementType::class)],
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'movement_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'supplier_delivery_id' => ['nullable', 'integer', 'exists:supplier_deliveries,id'],
            'distribution_center_id' => ['nullable', 'integer', 'exists:distribution_centers,id'],
            'delivery_person_id' => ['nullable', 'integer', 'exists:delivery_persons,id'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'order_id' => ['nullable', 'integer', 'exists:orders,id']
            ];
        }
    }
}