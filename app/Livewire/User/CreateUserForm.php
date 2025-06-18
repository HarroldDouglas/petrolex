<?php

namespace App\Livewire\User;

use App\DTOs\User\CreateUserDTO;
use App\Enums\UserRole;
use App\Http\Requests\User\StoreUserRequest;
use Dotenv\Exception\ValidationException;
use Illuminate\Foundation\Http\FormRequest;

class CreateUserForm extends AbstractUserForm
{
    protected function customRequest(): FormRequest
    {
        return new StoreUserRequest;
    }

    public function save()
    {
        $validatedData = $this->validate();
        $this->validateDistributionCenters();
        try {
            $dto = new CreateUserDTO(
                first_name: $validatedData['first_name'],
                last_name: $validatedData['last_name'],
                email: $validatedData['email'],
                phone_number: $validatedData['phone_number'],
                password: $validatedData['password'],
                address: $validatedData['address'] ?? null,
                is_active: $validatedData['is_active'] ?? true,
                role: UserRole::from($validatedData['role']),
                distribution_center_ids: $validatedData['distribution_center_ids'] ?? []
            );

            $user = $this->userService->create($dto);

            session()->flash('success', 'Utilisateur créé avec succès!');

            return redirect()->route('users.list');

        } catch (ValidationException $e) {
            $this->addError('email', $e->getMessage());
        } catch (\Exception $e) {
            session()->flash('error', 'Une erreur est survenue lors de la création de l\'utilisateur.');
        }
    }
}
