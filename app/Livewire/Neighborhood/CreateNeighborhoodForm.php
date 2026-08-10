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
            'name' => trim($validatedData['name']),
            'municipality_id' => $validatedData['municipalityId'],
            'is_active' => $validatedData['is_active'],
            'latitude' => (float) $validatedData['latitude'],
            'longitude' => (float) $validatedData['longitude'],
        ];

        $this->neighborhoodService->create($data);

        session()->flash('success', 'Quartier créé avec succès.');

        return redirect()->route('neighborhoods.index');
    }
}
