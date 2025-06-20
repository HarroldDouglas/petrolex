<?php

namespace App\Livewire\User;

use App\Enums\UserRole;
use App\Services\DistributionCenter\DistributionCenterService;
use App\Services\Shared\Media\MediaServiceInterface;
use App\Services\User\UserService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

abstract class AbstractUserForm extends Component
{
    use WithFileUploads;
    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public string $phone_number = '';
    public string $role = '';
    public string $password = '';
    public bool $is_active = true;

    /** @var array|UploadedFile */
    public $image = null;
    public $distribution_center_ids = [];
    public bool $showDistributionCenters = false;
    public bool $showPassword = false;

    public $allowedRoles = [];
    public array $availableDistributionCenters = [];

    protected $userService;
    protected $distributionCenterService;

    /** @var MediaServiceInterface */
    protected $mediaService;

    #[On('distribution-centers:selection-changed')]
    public function handleDistributionCentersUpdate($data): void
    {
        $this->distribution_center_ids = $data['selectedOptions'] ?? [];
    }

    public function mount(): void
    {
        $this->allowedRoles = UserRole::toArray();

        $this->availableDistributionCenters = $this->distributionCenterService->getAll()
            ->pluck('name', 'id')
            ->toArray();
    }

    public function boot(
        UserService $userService, DistributionCenterService $distributionCenterService, MediaServiceInterface $mediaService)
    {
        $this->userService = $userService;
        $this->distributionCenterService = $distributionCenterService;
        $this->mediaService = $mediaService;
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

    public function getImagePreviewStyleProperty(): string
    {
        if ($this->image) {
            if (is_string($this->image)) {
                $url = asset('storage/'.$this->image);
            } else {
                $url = $this->image->temporaryUrl();
            }

            return "background-image: url('{$url}');";
        }

        return '';
    }

    protected function validateDistributionCenters(): void
    {
        if (! $this->showDistributionCenters) {
            return;
        }
        if (count($this->distribution_center_ids) == 0) {
            $errorMessage = 'Veuillez sélectionner au moins un centre de distribution.';
            $this->addError('distribution_center_ids', $errorMessage);

            throw \Illuminate\Validation\ValidationException::withMessages([
                'distribution_center_ids' => [$errorMessage],
            ]);
        }
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
