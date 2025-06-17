<?php

namespace App\Livewire\User;

use App\Enums\UserRole;
use App\Services\DistributionCenter\DistributionCenterService;
use App\Services\User\UserService;
use Illuminate\Foundation\Http\FormRequest;
use Livewire\Component;

abstract class AbstractUserForm extends Component
{
    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public string $phone_number = '';
    public string $role = '';
    public string $password = '';
    public bool $is_active = true;
    public $distribution_center_ids = [];
    public bool $showDistributionCenters = false;
    public bool $showPassword = false;

    public $allowedRoles = [];
    public $availableDistributionCenters = [];

    protected $userService;
    protected $distributionCenterService;

    public function mount(): void
    {
        $this->allowedRoles = UserRole::toArray();

        $this->availableDistributionCenters = $this->distributionCenterService->getAll()
            ->pluck('name', 'id')
            ->toArray();
    }

    public function boot(UserService $userService, DistributionCenterService $distributionCenterService)
    {
        $this->userService = $userService;
        $this->distributionCenterService = $distributionCenterService;
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

    public function toggleDistributionCenters()
    {
        $allowedRoles = [
            UserRole::CENTER_MANAGER()->value,
            UserRole::DELIVERY_PERSON()->value,
        ];
        $this->showDistributionCenters = in_array($this->role, $allowedRoles);
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['role'])) {
            $this->toggleDistributionCenters();
        }

        $this->validateOnly($propertyName);
    }

    abstract public function save();

    public function render()
    {
        return view('livewire.user.user-form');
    }
}
