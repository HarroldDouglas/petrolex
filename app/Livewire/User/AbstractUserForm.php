<?php

namespace App\Livewire\User;

use App\DTOs\User\CreateUserDTO;
use App\Services\User\UserService;
use App\Services\DistributionCenter\DistributionCenterService;
use Livewire\Component;
use Illuminate\Validation\ValidationException;
use \App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

abstract class AbstractUserForm extends Component
{
    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public string $phone = '';
    public string $role = '';
    public string $password = '';
    public $distribution_centers = [];
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

    public function updatedRole()
    {
        $this->showDistributionCenters = $this->role === UserRole::CENTER_MANAGER()->value;
    }
    abstract public function save();

    public function render()
    {
        return view('livewire.user.user-form');
    }
}