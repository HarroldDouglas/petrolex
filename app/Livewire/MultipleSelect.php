<?php
namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Reactive;

class MultipleSelect extends Component
{
    #[Reactive]
    public array $options = [];
    
    public array $selectedOptions = [];

    public string $parentEvent = "options:selection-changed";

    public function mount(array $options = [], string $parentEvent, 
        array $selectedOptions = []): void
    {
        $this->options = $options;
        $this->selectedOptions = $selectedOptions;
        $this->parentEvent = $parentEvent ?? 'options:selection-changed';
    }

    public function rules(): array
    {
        return [
            'selectedOptions' => 'required|array|min:1',
            'selectedOptions.*' => 'integer|exists:distribution_centers,id'
        ];
    }
    public function messages(): array
    {
        return [
            'selectedOptions.required' => 'Please select at least one option.',
            'selectedOptions.min' => 'Please select at least one option.',
            'selectedOptions.*.exists' => 'One or more selected options are invalid.',
        ];
    }
    public function updateSelection($values): void
    {
        $this->validate();
        $this->selectedOptions = $values ?? [];
        $this->dispatch($this->parentEvent, ['selectedOptions' => $values]);
    }

    public function render()
    {
        return view('livewire.components.multiple-select');
    }
}