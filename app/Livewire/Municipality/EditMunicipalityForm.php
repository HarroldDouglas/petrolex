<?php

namespace App\Livewire\Municipality;

use App\Http\Requests\UpdateMunicipalityRequest;
use App\Models\Geography\Municipality;
use Illuminate\Foundation\Http\FormRequest;

class EditMunicipalityForm extends AbstractMunicipalityForm
{
    public function mount(Municipality $municipality)
    {
        $this->municipality = $municipality;

        $this->name = $this->municipality->name ?? '';
        $this->cityId = $this->municipality->city_id;
        $this->selectedNeighborhoods = $this->municipality->neighborhoods->pluck('id')->toArray();

        $this->initialize();
    }

    protected function customRequest(): FormRequest
    {
        return new UpdateMunicipalityRequest($this->municipality->id);
    }

    public function submit()
    {
        $validatedData = $this->validate();

        $data = [
            'name' => $validatedData['name'],
            'city_id' => $validatedData['cityId'],
        ];

        $this->municipalityService->updateMunicipality(
            $this->municipality,
            $data,
            $validatedData['selectedNeighborhoods'] ?? []
        );

        session()->flash('success', 'Municipalité mise à jour avec succès.');

        return redirect()->route('municipalities.index');
    }
}
