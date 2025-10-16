<?php

namespace App\Livewire\Role;

use App\Enums\UserRole;
use App\Services\Role\RoleService;
use HarroldWafo\LaravelCustomDatatable\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Spatie\Permission\Models\Role;

class RoleDataTable extends BaseDataTable
{
    protected $model = Role::class;

    protected const DEFAULT_SORT_FIELD = 'created_at';
    protected const DEFAULT_SORT_DIRECTION = 'desc';



    protected function getExportFileName(): string
    {
        return 'roles';
    }

    public function columns(): array
    {
        return [
            Column::make('ID', 'id')
                ->sortable()
                ->deselected(),

            Column::make('Nom', 'name')
                ->sortable()
                ->searchable()
                ->label(fn ($row) => $this->getRoleDisplayName($row->name)),

            Column::make('Permissions')
                ->label(function ($row) {
                    $count = $row->permissions_count ?? 0;
                    $badgeClass = $count > 0 ? 'bg-primary' : 'bg-secondary';

                    return new HtmlString(
                        '<span class="badge '.$badgeClass.'">'.$count.' permission(s)</span>'
                    );
                }),

            Column::make('Utilisateurs')
                ->label(function ($row) {
                    $count = $row->users_count ?? 0;
                    $badgeClass = $count > 0 ? 'bg-success' : 'bg-secondary';

                    return new HtmlString(
                        '<span class="badge '.$badgeClass.'">'.$count.' utilisateur(s)</span>'
                    );
                }),

            Column::make('Date de création', 'created_at')
                ->sortable()
                ->format(fn ($value) => $value->format('d/m/Y à H:i')),

            Column::make('Actions')
                ->label(
                    function ($row) {
                        return view('partials.roles.actions', ['role' => $row]);
                    }
                ),
        ];
    }

    public function builder(): Builder
    {
        return Role::query()
            ->withCount(['permissions', 'users']);
    }

    /**
     * Get display name for role (predefined or custom)
     */
    private function getRoleDisplayName(string $roleName): string
    {
       $enum = UserRole::tryFrom($roleName);

        if ($enum) {
            return $enum->label;
        }

        return ucwords(str_replace(['_', '-'], ' ', $roleName));
    }

    public function deleteRole(int $roleId)
    {
        try {
            $roleService = app(RoleService::class);
            
            $role = $roleService->findOrFail($roleId);

            if ($role->name === UserRole::SUPER_ADMIN()->value) {
                throw new \Exception('Le rôle Super Admin ne peut pas être supprimé.');
            }
 
            if ($role->users()->count() > 0) {
                throw new \Exception('Ce rôle ne peut pas être supprimé car il est assigné à des utilisateurs.');
            }

            $roleService->delete($role);

            $this->notify('Rôle supprimé avec succès.', 'success');
        } catch (\Exception $e) {
            Log::error('Error deleting role: '.$e->getMessage());

            $this->dispatch('show-notification', [
                'type' => 'error',
                'title' => 'Erreur !',
                'message' => $e->getMessage(),
                'timer' => 5000,
            ]);
        }
    }
}
