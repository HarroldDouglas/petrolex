<?php

namespace App\Livewire\Neighborhood;

use App\Http\Requests\Neighborhood\UpdateNeighborhoodRequest;
use App\Models\Geography\Neighborhood;
use Illuminate\Foundation\Http\FormRequest;

class EditNeighborhoodForm extends AbstractNeighborhoodForm
{
    public function mount(Neighborhood $neighborhood)
    {
        $this->neighborhood = $neighborhood;

        $this->name = $this->neighborhood->name ?? '';
        $this->municipalityId = $this->neighborhood->municipality_id;
        $this->cityId = $this->neighborhood->municipality->city_id ?? null;
        $this->is_active = $this->neighborhood->is_active;

        $this->initialize();
    }

    protected function customRequest(): FormRequest
    {
        return new UpdateNeighborhoodRequest($this->neighborhood->id);
    }

    public function submit()
    {
        $validatedData = $this->validate();

        $data = [
            'name' => $validatedData['name'],
            'municipality_id' => $validatedData['municipalityId'],
            'is_active' => $validatedData['is_active'],
        ];

        $this->neighborhoodService->update($this->neighborhood, $data);

        session()->flash('success', 'Quartier mis à jour avec succès.');

        return redirect()->route('neighborhoods.index');
    }
}
