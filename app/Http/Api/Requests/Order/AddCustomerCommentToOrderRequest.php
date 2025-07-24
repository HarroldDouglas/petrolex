<?php

declare(strict_types=1);

namespace App\Http\Api\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class AddCustomerCommentToOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'comments' => ['required', 'string', 'min:10'],
            'rating' => ['required', 'numeric', 'min:1', 'max:5'],
        ];
    }

    public function messages(): array
    {
        return [
            'comments.required' => 'Le commentaire est obligatoire.',
            'comments.string' => 'Le commentaire doit être une chaîne de caractères.',
            'comments.min' => 'Le commentaire doit contenir au moins :min caractères.',
            'rating.required' => 'La note est obligatoire.',
            'rating.numeric' => 'La note doit être un nombre.',
            'rating.min' => 'La note doit être au minimum de :min.',
            'rating.max' => 'La note doit être au maximum de :max.',
        ];
    }
}
