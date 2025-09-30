<?php

namespace App\Services\Role;

use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Services\BaseServiceForEntity;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class RoleService extends BaseServiceForEntity
{

    public function __construct(
        protected RoleRepositoryInterface $roleRepository
    ) {
        parent::__construct($this->roleRepository);
    }

    protected function getModel(): string
    {
        return Role::class;
    }

}