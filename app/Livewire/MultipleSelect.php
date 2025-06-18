<?php

namespace App\Livewire;

use Livewire\Attributes\Reactive;
use Livewire\Component;

class MultipleSelect extends Component
{
    #[Reactive]
    public array $options = [];

    public array $selectedOptions = [];

    public string $parentEvent = 'options:selection-changed';

    public $hasErrors = false;

    public function mount(array $options, string $parentEvent,
        array $selectedOptions = []): void
    {
        $this->options = $options;
        $this->selectedOptions = $selectedOptions;
        $this->parentEvent = $parentEvent;
    }

    public function updateSelection($values): void
    {
        $this->selectedOptions = $values ?? [];
        $this->dispatch($this->parentEvent, ['selectedOptions' => $values]);
    }

    public function render()
    {
        return view('livewire.components.multiple-select');
    }
}
