<?php

namespace App\Livewire\User;

use App\DTOs\BottleType\UserDTO;
use App\DTOs\BottleType\CreateBottleTypeDTO;
use App\DTOs\User\CreateUserDTO;
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
        try {
            
            $this->validate();

            $dto = CreateUserDTO::from([
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'email' => $this->email,
                'phone' => $this->phone,
                'role' => $this->role,
                'distribution_centers' => $this->showDistributionCenters ?: null
            ]);

            $user = $this->userService->createUser($dto);

            session()->flash('success', 'Utilisateur créé avec succès!');
            
            return redirect()->route('users.list');

        } catch (ValidationException $e) {
            $this->addError('email', $e->getMessage());
        } catch (\Exception $e) {
            session()->flash('error', 'Une erreur est survenue lors de la création de l\'utilisateur.');
        }
    }
}
