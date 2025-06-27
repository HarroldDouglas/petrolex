<?php

namespace App\Livewire\User;

use App\DTOs\User\UpdateUserDTO;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use Dotenv\Exception\ValidationException;
use Illuminate\Foundation\Http\FormRequest;

class EditUserForm extends AbstractUserForm
{
    public $user;
    public $id;

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
        /** @var \Spatie\Permission\Models\Role|null $role */
        $role = $user->roles->first();
        $this->role = $role?->name;
        $this->existingImage = $user->getMedia('images')->first()?->getUrl() ?? null;
        $this->distribution_center_ids = $user->activeDistributionCenters->pluck('id')?->toArray();

        $this->is_active = $user->is_active;
        $this->toggleDistributionCenters();
    }

    public function save()
    {
        $validatedData = $this->validate();
        $this->validateDistributionCenters();
        $validatedData['distribution_center_ids'] = $this->showDistributionCenters
            ? $validatedData['distribution_center_ids'] : [];
        try {
            $dto = UpdateUserDTO::fromArray($validatedData);

            $dtoArray = $dto->toArray();

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
