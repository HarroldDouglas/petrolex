<?php

declare(strict_types=1);

namespace App\Http\Api\Requests\DistributionCenter;

use Illuminate\Foundation\Http\FormRequest;

class GetClosestDistributionCenterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'latitude' => ['required_without:neighborhood_id', 'numeric', 'between:-90,90'],
            'longitude' => ['required_without:neighborhood_id', 'numeric', 'between:-180,180'],
            'neighborhood_id' => ['required_without:latitude,longitude', 'integer', 'exists:neighborhoods,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'latitude.required_without' => 'La latitude est requise si aucun ID de quartier n\'est fourni.',
            'latitude.numeric' => 'La latitude doit être un nombre.',
            'latitude.between' => 'La latitude doit être comprise entre -90 et 90.',
            'longitude.required_without' => 'La longitude est requise si aucun ID de quartier n\'est fourni.',
            'longitude.numeric' => 'La longitude doit être un nombre.',
            'longitude.between' => 'La longitude doit être comprise entre -180 et 180.',
            'neighborhood_id.required_without' => 'L\'ID du quartier est requis si les coordonnées ne sont pas fournies.',
            'neighborhood_id.integer' => 'L\'ID du quartier doit être un nombre entier.',
            'neighborhood_id.exists' => 'Le quartier spécifié n\'existe pas.',
        ];
    }
}
