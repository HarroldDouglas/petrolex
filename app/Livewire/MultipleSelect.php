<?php
namespace App\Livewire;

use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\Attributes\Reactive;

class MultipleSelect extends Component
{
    #[Reactive]
    public array $items = [];
    
    public array $selected = [];
    
    public string $parentEvent;

    public function mount(array $items = [], string $parentEvent = null, array $selectedItems = []): void
    {
        $this->items = $items;
        $this->selected = $selectedItems;
        $this->parentEvent = $parentEvent ?? 'items:selection-changed';
    }

    public function updateSelection($values): void
    {
        $this->selected = $values;
        Log::info('MultipleSelect updated', [
            'selected' => $this->selected,
            'parent_event' => $this->parentEvent,
        ]);
        $this->dispatch($this->parentEvent, ['selected' => $values]);
    }

    public function render()
    {
        return view('livewire.components.multiple-select');
    }
}