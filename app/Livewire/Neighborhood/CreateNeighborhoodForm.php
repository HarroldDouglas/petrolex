<?php

namespace App\Livewire\Neighborhood;

use App\Http\Requests\Neighborhood\StoreNeighborhoodRequest;
use Illuminate\Foundation\Http\FormRequest;

class CreateNeighborhoodForm extends AbstractNeighborhoodForm
{
    public function mount()
    {
        $this->initialize();
    }

    protected function customRequest(): FormRequest
    {
        return new StoreNeighborhoodRequest;
    }

    public function submit()
    {
        $validatedData = $this->validate();

        $data = [
            'name' => $validatedData['name'],
            'municipality_id' => $validatedData['municipalityId'],
            'is_active' => $validatedData['is_active'],
        ];

        $this->neighborhoodService->create($data);

        session()->flash('success', 'Quartier créé avec succès.');

        return redirect()->route('neighborhoods.index');
    }
}
