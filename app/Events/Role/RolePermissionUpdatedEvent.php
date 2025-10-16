<?php

namespace App\Events\Role;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Spatie\Permission\Models\Role;

class RolePermissionUpdatedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Role $role,
        public array $permissions
    ) {}
}