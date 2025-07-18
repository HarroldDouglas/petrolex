<?php

namespace App\Livewire\DistributionCenter;

use App\Http\Requests\UpdateDistributionCenterRequest;
use App\Models\DistributionCenter;
use App\Models\Geography\City;
use App\Models\Geography\Country;
use App\Models\Geography\Neighborhood;
use Illuminate\Foundation\Http\FormRequest;

class EditDistributionCenter extends AbstractDistributionCenterForm
{
    public $distributionCenter;

    public function mount(DistributionCenter $distributionCenter)
    {
        $this->distributionCenter = $distributionCenter;
        $this->name = $distributionCenter->name;
        $this->neighborhoodId = $distributionCenter->neighborhood_id;

        if ($this->neighborhoodId) {
            /** @var Neighborhood $neighborhood */
            $neighborhood = $distributionCenter->neighborhood;
            if ($neighborhood) {
                /** @var City $city */
                $city = $neighborhood->municipality->city;
                if ($city) {
                    $this->cityId = $city->id;
                    /** @var Country $country */
                    $country = $city->country;
                    $this->countryId = $country?->id;
                }
            }
        }

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

        $this->distributionCenter->update($validatedData);

        session()->flash('success', 'Centre de distribution mis à jour avec succès.');

        return redirect()->route('distribution-centers.list');
    }
}
