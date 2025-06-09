<?php

namespace App\Livewire\DistributionCenter;

use App\Http\Requests\UpdateDistributionCenterRequest;
use App\Models\DistributionCenter;
use Illuminate\Foundation\Http\FormRequest;

class EditDistributionCenter extends AbstractDistributionCenterForm
{
    public $distributionCenter;

    public function mount(DistributionCenter $distributionCenter)
    {
        $this->distributionCenter = $distributionCenter;
        $this->name = $distributionCenter->name;
        $this->country = $distributionCenter->country;
        $this->city = $distributionCenter->city;
        $this->neighborhood = $distributionCenter->neighborhood;
        $this->address = $distributionCenter->address;
        $this->phone = $distributionCenter->phone;
        $this->email = $distributionCenter->email;
        $this->latitude = $distributionCenter->latitude;
        $this->longitude = $distributionCenter->longitude;
        $this->storage_capacity = $distributionCenter->storage_capacity;
        $this->is_active = $distributionCenter->is_active;
        $this->description = $distributionCenter->description;
    }

    protected function customRequest(): FormRequest
    {
        return new UpdateDistributionCenterRequest($this->distributionCenter->id);
    }

    public function submit()
    {
        $validatedData = $this->validate();

        $this->distributionCenter->update($validatedData);

        session()->flash('success', 'Centre de distribution mis à jour avec succès.');

        return redirect()->route('distribution-centers.list');
    }
}
