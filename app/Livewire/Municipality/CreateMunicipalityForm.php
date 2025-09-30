<?php

namespace App\Livewire\Municipality;

use App\Http\Requests\StoreMunicipalityRequest;
use Illuminate\Foundation\Http\FormRequest;

class CreateMunicipalityForm extends AbstractMunicipalityForm
{
    public function mount()
    {
        $this->initialize();
    }

    protected function customRequest(): FormRequest
    {
        return new StoreMunicipalityRequest;
    }

    public function submit()
    {
        $validatedData = $this->validate();

        $data = [
            'name' => $validatedData['name'],
            'city_id' => $validatedData['cityId'],
        ];

        $this->municipalityService->createMunicipality($data, $validatedData['selectedNeighborhoods'] ?? []);

        session()->flash('success', 'Municipalité créée avec succès.');

        $this->reset('name', 'cityId', 'selectedNeighborhoods');

        return redirect()->route('municipalities.index');
    }
}
