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

        // Set neighborhoodId directly from the model
        $this->neighborhoodId = $distributionCenter->neighborhood_id;

        // Derive cityId, and countryId from the neighborhood relationship
        if ($this->neighborhoodId) {
            /** @var \App\Models\Geography\Neighborhood $neighborhood */
            $neighborhood = $distributionCenter->neighborhood;
            if ($neighborhood) {
                /** @var \App\Models\Geography\Municipality $municipality */
                $municipality = $neighborhood->municipality;
                if ($municipality) {
                    /** @var \App\Models\Geography\City $city */
                    $city = $municipality->city;
                    if ($city) {
                        $this->cityId = $city->id;
                        /** @var \App\Models\Geography\Country $country */
                        $country = $city->country;
                        if ($country) {
                            $this->countryId = $country->id;
                        }
                    }
                }
            }
        }

        // Trigger updates to load dependent dropdowns
        $this->updated('countryId');
        $this->updated('cityId');

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

        // The model now expects neighborhood_id directly
        $this->distributionCenter->update(array_merge($validatedData, [
            'neighborhood_id' => $this->neighborhoodId,
        ]));

        session()->flash('success', 'Centre de distribution mis à jour avec succès.');

        return redirect()->route('distribution-centers.list');
    }
}
