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
        $this->neighborhood_id = $distributionCenter->neighborhood_id;

        // Derive city_id, and country_id from the neighborhood relationship
        if ($this->neighborhood_id) {
            /** @var Neighborhood $neighborhood */
            $neighborhood = $distributionCenter->neighborhood;
            if ($neighborhood) {
                /** @var City $city */
                $city = $neighborhood->municipality->city;
                if ($city) {
                    $this->city_id = $city->id;
                    /** @var Country $country */
                    $country = $city->country;
                    $this->country_id = $country?->id;
                }
            }
        }

        if ($this->country_id) {
            $this->availableCities = $this->geographyRepository->getCitiesByCountryId($this->country_id);
        }
        if ($this->city_id) {
            $this->availableNeighborhoods = $this->geographyRepository->getNeighborhoodsByCityId($this->city_id);
        }

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
