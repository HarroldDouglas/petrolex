<?php

namespace App\Livewire\User;

use App\Enums\UserRole;
use App\Services\DistributionCenter\DistributionCenterService;
use App\Services\Geography\CountryService;
use App\Services\Shared\Media\MediaServiceInterface;
use App\Services\User\UserService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Features\SupportFileUploads\WithFileUploads;

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
    public string|UploadedFile|null $image = null;
    public $distribution_center_ids = [];
    public bool $showDistributionCenters = false;
    public bool $showPassword = false;

    public string $country_code = '';
    public array $countries = [];

    public string $language = '';
    public array $languages = [];

    public $allowedRoles = [];
    public array $availableDistributionCenters = [];

    protected $userService;
    protected $distributionCenterService;
    protected $countryService;

    /** @var MediaServiceInterface */
    protected $mediaService;

    #[On('distribution-centers:selection-changed')]
    public function handleDistributionCentersUpdate($data): void
    {
        $this->distribution_center_ids = $data['selectedOptions'] ?? [];
    }

    public function initialize()
    {
        $this->allowedRoles = array_diff(UserRole::toArray(), [UserRole::CUSTOMER()->value]);
        $this->availableDistributionCenters = $this->distributionCenterService->getAll()
            ->pluck('name', 'id')
            ->toArray();

        $this->countries = $this->countryService->getCodeNameList();
        $this->country_code = $this->country_code ?: config('countries.default_country_code');

        $this->languages = collect(\App\Enums\Language::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->label])
            ->toArray();
        $this->language = $this->language ?: config('countries.default_language', 'fr');
    }

    public function boot(
        UserService $userService,
        DistributionCenterService $distributionCenterService,
        MediaServiceInterface $mediaService,
        CountryService $countryService
    ) {
        $this->userService = $userService;
        $this->distributionCenterService = $distributionCenterService;
        $this->mediaService = $mediaService;
        $this->countryService = $countryService;
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
        $image = $this->image;

        if ($image) {
            if (is_string(value: $image)) {
                $url = $image;
            } elseif ($image instanceof TemporaryUploadedFile) {
                $url = $image->temporaryUrl();
            } else {
                return '';
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

        if (in_array($propertyName, ['phone_number', 'country_code'])) {
            $this->normalizePhoneNumber();
        }

        $this->validateOnly($propertyName);
    }

    public function validate($rules = null, $messages = null, $attributes = null): array
    {
        $this->normalizePhoneNumber();

        return parent::validate($rules, $messages, $attributes);
    }

    protected function normalizePhoneNumber(): void
    {
        if ($this->phone_number === '' || $this->country_code === '') {
            return;
        }

        $phone = trim($this->phone_number);
        $phoneCode = \App\Models\Geography\Country::where('code', strtoupper($this->country_code))
            ->value('phone_code');

        if (! $phoneCode) {
            return;
        }

        // Accept the prefix with or without the leading +
        $variants = [$phoneCode, ltrim($phoneCode, '+')];

        foreach ($variants as $variant) {
            if ($variant !== '' && str_starts_with($phone, $variant)) {
                $this->phone_number = substr($phone, strlen($variant));

                return;
            }
        }
    }

    abstract public function save();

    public function render()
    {
        return view('livewire.user.user-form');
    }
}
