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

        // Derive cityId, and countryId from the neighborhood relationship
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

        if ($this->countryId) {
            $this->availableCities = $this->geographyRepository->getCitiesByCountryId($this->countryId);
        }
        if ($this->cityId) {
            $this->availableNeighborhoods = $this->geographyRepository->getNeighborhoodsByCityId($this->cityId);
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

        $validatedData['neighborhood_id'] = $this->neighborhoodId;

        $this->distributionCenter->update($validatedData);

        session()->flash('success', 'Centre de distribution mis à jour avec succès.');

        return redirect()->route('distribution-centers.list');
    }
}
