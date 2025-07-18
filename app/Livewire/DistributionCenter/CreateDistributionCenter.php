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

        // The model now expects neighborhood_id directly
        DistributionCenter::create(array_merge($validatedData, [
            'neighborhood_id' => $this->neighborhoodId,
        ]));

        session()->flash('success', 'Centre de distribution créé avec succès.');

        return redirect()->route('distribution-centers.list');
    }
}
