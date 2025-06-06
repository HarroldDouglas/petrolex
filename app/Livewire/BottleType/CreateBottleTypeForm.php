<?php

namespace App\Livewire\BottleType;

use App\DTOs\BottleType\CreateBottleTypeDTO;
use App\Services\BottleType\BottleTypeService;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Component;

class CreateBottleTypeForm extends Component
{
    #[Validate]
    public string $name = '';
    #[Validate]
    public string $capacity = '';
    #[Validate]
    public string $bottle_price = '';
    #[Validate]
    public string $content_price = '';
    #[Validate]
    public string $bottle_with_content_price = '';
    #[Validate]
    public ?string $description = null;
    #[Validate]
    public ?string $height = null;
    #[Validate]
    public ?string $width = null;
    #[Validate]
    public ?string $radius = null;
    #[Validate]
    public bool $is_active = true;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255',
                Rule::unique('bottle_types', 'name'),
            ],
            'capacity' => ['required', 'numeric', 'min:0'],
            'height' => ['nullable', 'numeric', 'min:0'],
            'width' => ['nullable', 'numeric', 'min:0'],
            'radius' => ['nullable', 'numeric', 'min:0'],
            'content_price' => ['required', 'numeric', 'min:0'],
            'bottle_with_content_price' => ['required', 'numeric', 'min:0', 'gt:content_price'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    protected function messages()
    {
        return [
            'name.required' => 'Le nom du type de bouteille est requis.',
            'name.string' => 'Le nom du type de bouteille doit être une chaîne de caractères.',
            'name.unique' => 'Ce nom de type de bouteille existe déjà.',
            'name.max' => 'Ce nom de type de bouteille ne doit pas dépasser 255 caractères.',

            'capacity.required' => 'La capacité est requise.',
            'capacity.numeric' => 'La capacité doit être un nombre.',
            'capacity.min' => 'La capacité doit être au moins 0.',

            'height.numeric' => 'La hauteur doit être un nombre.',
            'height.min' => 'La hauteur doit être au moins 0.',

            'width.numeric' => 'La largeur doit être un nombre.',
            'width.min' => 'La largeur doit être au moins 0.',

            'radius.numeric' => 'Le rayon doit être un nombre.',
            'radius.min' => 'Le rayon doit être au moins 0.',

            'content_price.required' => 'Le prix du contenu est requis.',
            'content_price.numeric' => 'Le prix du contenu doit être un nombre.',
            'content_price.min' => 'Le prix du contenu doit être au moins 0.',

            'bottle_with_content_price.required' => 'Le prix de la bouteille avec contenu est requis.',
            'bottle_with_content_price.numeric' => 'Le prix de la bouteille avec contenu doit être un nombre.',
            'bottle_with_content_price.min' => 'Le prix de la bouteille avec contenu doit être au moins 0.',
            'bottle_with_content_price.gt' => 'Le prix de la bouteille avec contenu doit être supérieur au prix du contenu.',
            'is_active.boolean' => 'Le statut doit être vrai ou faux.',
        ];
    }

    public function save()
    {
        Log::info('saving Bottle Type in Livewire Compnent', [$this->validate()]);
        $validated = $this->validate();
        Log::info('after validation Bottle Type in Livewire Compnent');
        $bottleTypeService = app(BottleTypeService::class);
        $bottleTypeDTO = new CreateBottleTypeDTO(
            name: $validated['name'],
            capacity: $validated['capacity'],
            content_price: $validated['content_price'],
            bottle_with_content_price: $validated['bottle_with_content_price'],
            is_active: $validated['is_active'],
            description: $this->description,
            height: $this->height ? (float) $this->height : null,
            width: $this->width ? (float) $this->width : null,
            radius: $this->radius ? (float) $this->radius : null
        );
        $bottleTypeService->create($bottleTypeDTO);
        $this->dispatch('success', message: 'Type de bouteille créé avec succès.');
        redirect()->route('bottles.types');
    }

    public function render()
    {
        return view('livewire.bottle-type.create-bottle-type-form');
    }
}
