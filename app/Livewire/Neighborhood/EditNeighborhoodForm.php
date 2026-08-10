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
        $this->latitude = $this->neighborhood->latitude !== null ? (string) $this->neighborhood->latitude : null;
        $this->longitude = $this->neighborhood->longitude !== null ? (string) $this->neighborhood->longitude : null;
        $this->hasPolygon = ! empty($this->neighborhood->polygon);

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
            'name' => trim($validatedData['name']),
            'municipality_id' => $validatedData['municipalityId'],
            'is_active' => $validatedData['is_active'],
            'latitude' => (float) $validatedData['latitude'],
            'longitude' => (float) $validatedData['longitude'],
        ];

        $this->neighborhoodService->update($this->neighborhood, $data);

        session()->flash('success', 'Quartier mis à jour avec succès.');

        return redirect()->route('neighborhoods.index');
    }
}
