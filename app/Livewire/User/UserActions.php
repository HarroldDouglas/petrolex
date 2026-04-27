<?php

namespace App\Livewire\User;

use App\DTOs\User\UpdateUserDTO;
use App\Models\User;
use App\Services\User\UserService;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class UserActions extends Component
{
    public User $user;

    public function mount(User $user): void
    {
        $this->user = $user;
    }

    public function toggleUserStatus(): void
    {
        try {
            $userService = app(UserService::class);
            $newStatus = ! $this->user->is_active;

            $userService->update(
                $this->user,
                (new UpdateUserDTO(is_active: $newStatus))->toArrayFiltered()
            );

            $this->user->refresh();

            $status = $newStatus ? 'activé' : 'désactivé';

            $this->dispatch('show-notification', [
                'type' => 'success',
                'title' => 'Statut modifié !',
                'message' => "L'utilisateur {$this->user->full_name} a été {$status} avec succès.",
                'timer' => 3000,
            ]);
        } catch (\Exception $e) {
            Log::error('Error toggling user status from details page', [
                'userId' => $this->user->id,
                'error' => $e->getMessage(),
            ]);

            $this->dispatch('show-notification', [
                'type' => 'error',
                'title' => 'Erreur !',
                'message' => "Une erreur s'est produite lors de la modification du statut.",
                'timer' => 3000,
            ]);
        }
    }

    public function deleteUser()
    {
        try {
            $userService = app(UserService::class);
            $name = $this->user->full_name;

            $userService->delete($this->user);

            session()->flash('success', "L'utilisateur {$name} a été supprimé avec succès.");

            return redirect()->route('users.list');
        } catch (\Exception $e) {
            Log::error('Error deleting user from details page', [
                'userId' => $this->user->id,
                'error' => $e->getMessage(),
            ]);

            $this->dispatch('show-notification', [
                'type' => 'error',
                'title' => 'Erreur !',
                'message' => "Une erreur s'est produite lors de la suppression.",
                'timer' => 3000,
            ]);
        }
    }

    public function render()
    {
        return view('livewire.user.user-actions');
    }
}
