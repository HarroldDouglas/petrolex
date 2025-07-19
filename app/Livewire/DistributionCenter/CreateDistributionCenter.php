<?php

namespace App\Livewire\DistributionCenter;

use App\Http\Requests\StoreDistributionCenterRequest;
use App\Models\DistributionCenter;
use Illuminate\Foundation\Http\FormRequest;

class CreateDistributionCenter extends AbstractDistributionCenterForm
{
    protected function customRequest(): FormRequest
    {
        return new StoreDistributionCenterRequest;
    }

    public function submit()
    {
        $validatedData = $this->validate();

        $validatedData['neighborhood_id'] = $this->neighborhoodId;

        DistributionCenter::create($validatedData);

        session()->flash('success', 'Centre de distribution créé avec succès.');

        return redirect()->route('distribution-centers.list');
    }
}
