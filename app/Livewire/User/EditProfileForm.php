<?php

namespace App\Livewire\User;

use App\DTOs\User\UpdateProfileDTO;
use App\Enums\UserRole;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Models\User;
use App\Services\Shared\Media\MediaServiceInterface;
use App\Services\User\UserService;
use Dotenv\Exception\ValidationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Features\SupportFileUploads\WithFileUploads;

class EditProfileForm extends Component
{
    use WithFileUploads;
    public User $user;
    public int $id;
    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public string $phone_number = '';
    public string $password = '';
    public string|UploadedFile|null $image = null;
    public bool $showPassword = false;

    protected $userService;

    /** @var MediaServiceInterface */
    protected $mediaService;

    public function boot(
        UserService $userService, MediaServiceInterface $mediaService)
    {
        $this->userService = $userService;
        $this->mediaService = $mediaService;
    }

    public function mount(User $user)
    {
        $this->id = $user->id;
        $this->user = $user;
        $this->first_name = $user->first_name;
        $this->last_name = $user->last_name;
        $this->email = $user->email;
        $this->phone_number = $user->phone_number;
        $this->image = $user->getMedia('images')->first()?->getUrl() ?? null;
    }

    public function getIsEditableProperty()
    {
        $allowedRoles = [
            UserRole::SUPER_ADMIN()->value,
            UserRole::ADMIN()->value,
            UserRole::MANAGER()->value,
            UserRole::ACCOUNTANT()->value,
            UserRole::GAS_MANAGER()->value,
        ];

        return $this->user->hasAnyRole($allowedRoles);
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

    protected function customRequest(): FormRequest
    {
        return new UpdateProfileRequest($this->user->id);
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

    public function save()
    {
        $validatedData = $this->validate();
        try {
            $validatedData['image'] = is_string($validatedData['image']) ? null : $validatedData['image'];

            $dto = UpdateProfileDTO::from($validatedData);
            $dtoArray = $dto->toArray();

            /** @var User */
            $user = $this->userService->updateProfile($this->user, $dtoArray);

            session()->flash('success', 'Votre profil a été mis à jour avec succès!');

            return redirect()->route('users.profile');

        } catch (ValidationException $e) {
            $this->addError('email', $e->getMessage());
        } catch (\Exception $e) {
            session()->flash('error', 'Une erreur est survenue lors de la mise jour de l\'utilisateur.');
        }
    }

    public function render()
    {
        return view('livewire.user.profile-form');
    }
}
