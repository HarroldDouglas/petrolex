<?php

namespace App\Livewire\User;

use App\DTOs\User\UpdateUserDTO;
use App\Enums\UserRole;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use Dotenv\Exception\ValidationException;
use Illuminate\Foundation\Http\FormRequest;

class EditUserForm extends AbstractUserForm
{
    public $user;
    public $id;
    public $existingImages = [];

    protected function customRequest(): FormRequest
    {
        return new UpdateUserRequest($this->user->id);
    }

    public function mount(User $user)
    {
        parent::initialize();

        $this->id = $user->id;
        $this->user = $user;
        $this->first_name = $user->first_name;
        $this->last_name = $user->last_name;
        $this->email = $user->email;
        $this->phone_number = $user->phone_number;
        $this->role = $user->roles->first()?->name;;
        $this->image = $user->getMedia('images')->first()?->getUrl() ?? null;
        $this->distribution_center_ids = $user->activeDistributionCenters->pluck('id')?->toArray();

        $this->is_active = $user->is_active;

        $this->existingImages = $this->mediaService->getAllImagesForModel($user) ?? [];
    }

    public function save()
    {
       
        $validatedData = $this->validate();
        $this->validateDistributionCenters();
 
        try {

            $dto = new UpdateUserDTO(
                id: $this->user->id,
                first_name: $validatedData['first_name'],
                last_name: $validatedData['last_name'],
                email: $validatedData['email'],
                phone_number: $validatedData['phone_number'],
                password: $validatedData['password'],
                address: $validatedData['address'] ?? null,
                is_active: $validatedData['is_active'] ?? true,
                role: UserRole::from($validatedData['role']),
                distribution_center_ids: $validatedData['distribution_center_ids'] ?? [],
                image: $validatedData['image']
            );

            $dtoArray = $dto->toArray();

            $dtoArray['image'] = $this->image;

            /** @var User */
            $user = $this->userService->update($this->user, $dtoArray);

            session()->flash('success', 'Utilisateur mis à jour avec succès!');

            return redirect()->route('users.list');

        } catch (ValidationException $e) {
            $this->addError('email', $e->getMessage());
        } catch (\Exception $e) {
            session()->flash('error', 'Une erreur est survenue lors de la mise jour de l\'utilisateur.');
        }
    }
}
