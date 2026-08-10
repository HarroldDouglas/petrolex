<?php

namespace App\Http\Api\Requests\App;

use Illuminate\Foundation\Http\FormRequest;

class StoreMobileAppLogRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'app_type' => ['required', 'string', 'in:customer_app,delivery_app,manager_app'],
            'platform' => ['nullable', 'string', 'in:android,ios'],
            'app_version' => ['nullable', 'string', 'max:20'],
            'device_model' => ['nullable', 'string', 'max:100'],
            'os_version' => ['nullable', 'string', 'max:50'],
            'level' => ['nullable', 'string', 'in:error,warning,info'],
            'message' => ['required', 'string', 'max:2000'],
            'stack_trace' => ['nullable', 'string', 'max:20000'],
            'context' => ['nullable', 'array'],
            'occurred_at' => ['nullable', 'date'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'app_type.required' => "Le type d'application est obligatoire.",
            'app_type.in' => "Le type d'application est invalide.",
            'platform.in' => 'La plateforme doit être android ou ios.',
            'level.in' => 'Le niveau doit être error, warning ou info.',
            'message.required' => 'Le message est obligatoire.',
            'message.max' => 'Le message ne peut pas dépasser 2000 caractères.',
            'stack_trace.max' => 'La stack trace ne peut pas dépasser 20000 caractères.',
        ];
    }
}
