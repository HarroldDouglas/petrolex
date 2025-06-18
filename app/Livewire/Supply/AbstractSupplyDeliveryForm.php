<?php

namespace App\Livewire\Supply;

use App\Repositories\Contracts\DistributionCenterRepositoryInterface;
use App\Services\DistributionCenter\DistributionCenterService;
use App\Services\Supply\SupplyDeliveryService;
use Illuminate\Foundation\Http\FormRequest;
use Livewire\Component;

abstract class AbstractSupplyDeliveryForm extends Component
{
    public $title;
    public $supply_date;
    public $description;
    public $distribution_center_id;
    public bool $is_active = true;

    protected $supplyDeliveryService;
    protected $distributionCenterRepository;
    protected $distributionCenters;

    public function boot(
        SupplyDeliveryService $supplyDeliveryService,
        DistributionCenterRepositoryInterface $distributionCenterRepository
    ) {
        $this->supplyDeliveryService = $supplyDeliveryService;
        $this->distributionCenterRepository = $distributionCenterRepository;
        $this->loadDistributionCenters();
    }

    protected function loadDistributionCenters()
    {
        $centers = DistributionCenterService::getForCurrentUser();
        $this->distributionCenters = $centers->map(function ($center) {
            return [
                'id' => $center->id,
                'name' => $center->name,
            ];
        })->toArray();
    }

    public function rules()
    {
        // @phpstan-ignore-next-line
        return $this->customRequest()->rules();
    }

    public function messages()
    {
        return $this->customRequest()->messages();
    }

    /**
     * Get the request class for validation
     */
    abstract protected function customRequest(): FormRequest;

    public function render()
    {
        return view('livewire.supply.supply-form', [
            'distributionCenters' => $this->distributionCenters,
        ]);
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function resetForm()
    {
        $this->title = '';
        $this->supply_date = now()->format('Y-m-d\TH:i');
        $this->description = '';
        $this->distribution_center_id = '';
        $this->is_active = true;
    }

    abstract public function save();
}
