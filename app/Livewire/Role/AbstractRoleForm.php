<?php

namespace App\Livewire\Role;

use App\Enums\PermissionEnum;
use App\Services\Role\RoleService;
use Illuminate\Foundation\Http\FormRequest;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

abstract class AbstractRoleForm extends Component
{
    public ?Role $role = null;
    public string $name = '';
    public string $guard_name = 'web';
    public array $selectedPermissions = [];
    public array $availablePermissions = [];

    protected RoleService $roleService;

    public function boot(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    public function initialize()
    {
        $this->loadAvailablePermissions();
        
        if ($this->role) {
            $this->name = $this->role->name;
            $this->guard_name = $this->role->guard_name;
            $this->selectedPermissions = $this->role->permissions->pluck('name')->toArray();
        }
    }

    protected function loadAvailablePermissions()
    {
        $this->availablePermissions = Permission::all()
            ->groupBy(function ($permission) {
                // Group permissions by module (e.g., 'users.view' -> 'Users')
                $parts = explode('.', $permission->name);
                $module = $parts[0];
                
                // Map module names to proper display names
                $moduleNames = [
                    'users' => 'Gestion des utilisateurs',
                    'roles' => 'Gestion des rôles',
                    'distribution_centers' => 'Centres de distribution',
                    'orders' => 'Gestion des commandes',
                    'deliveries' => 'Gestion des livraisons',
                    'products' => 'Gestion des produits',
                    'supplier_deliveries' => 'Livraisons fournisseurs',
                    'customers' => 'Gestion des clients',
                    'payments' => 'Gestion des paiements',
                    'reports' => 'Rapports et analyses',
                    'gas' => 'Gestion du gaz',
                    'comments' => 'Gestion des commentaires',
                    'mobile' => 'Accès mobile',
                    'delivery' => 'Suivi des livraisons',
                    'profile' => 'Profil utilisateur',
                    'history' => 'Historique',
                    'municipalities' => 'Gestion des municipalités',
                ];
                
                return $moduleNames[$module] ?? ucfirst($module);
            })
            ->map(function ($permissions, $module) {
                return [
                    'module' => $module,
                    'permissions' => $permissions->map(function ($permission) {
                        return [
                            'name' => $permission->name,
                            'label' => $this->formatPermissionLabel($permission->name)
                        ];
                    })->toArray()
                ];
            })
            ->toArray();
    }

    protected function formatPermissionLabel(string $permissionName): string
    {
        $labels = PermissionEnum::labels();
        return $labels[$permissionName] ?? ucfirst(str_replace(['.', '_'], ' ', $permissionName));
    }

    public function toggleGroup(string $module)
    {
        $modulePermissions = collect($this->availablePermissions[$module]['permissions'])
            ->pluck('name')
            ->toArray();
        
        $selectedInModule = array_intersect($this->selectedPermissions, $modulePermissions);
        
        if (count($selectedInModule) === count($modulePermissions)) {
            // All selected, so unselect all
            $this->selectedPermissions = array_diff($this->selectedPermissions, $modulePermissions);
        } else {
            // Not all selected, so select all
            $this->selectedPermissions = array_unique(array_merge($this->selectedPermissions, $modulePermissions));
        }
    }

    public function isGroupFullySelected(string $module): bool
    {
        if (!isset($this->availablePermissions[$module])) {
            return false;
        }
        
        $modulePermissions = collect($this->availablePermissions[$module]['permissions'])
            ->pluck('name')
            ->toArray();
        
        return count(array_intersect($this->selectedPermissions, $modulePermissions)) === count($modulePermissions);
    }

    public function isGroupPartiallySelected(string $module): bool
    {
        if (!isset($this->availablePermissions[$module])) {
            return false;
        }
        
        $modulePermissions = collect($this->availablePermissions[$module]['permissions'])
            ->pluck('name')
            ->toArray();
        
        $selectedInModule = array_intersect($this->selectedPermissions, $modulePermissions);
        return count($selectedInModule) > 0 && count($selectedInModule) < count($modulePermissions);
    }

    abstract protected function customRequest(): FormRequest;

    protected function rules(): array
    {
        return $this->customRequest()->rules();
    }

    protected function messages(): array
    {
        return array_merge($this->customRequest()->messages(), [
            'selectedPermissions.*.exists' => 'Une ou plusieurs permissions sélectionnées n\'existent pas.',
        ]);
    }

    public function render()
    {
        return view('livewire.role.role-form');
    }
}