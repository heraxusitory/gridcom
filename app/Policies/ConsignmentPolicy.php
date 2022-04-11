<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ConsignmentPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function view(User $user): bool
    {
        return $user->hasPermissionTo(Permission::buildPermissionName([Permission::RESOURCE_CONSIGNMENTS, Permission::ACTION_VIEW]));
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::buildPermissionName([Permission::RESOURCE_CONSIGNMENTS, Permission::ACTION_CREATE]));
    }

    public function update(User $user): bool
    {
        return $user->hasPermissionTo(Permission::buildPermissionName([Permission::RESOURCE_CONSIGNMENTS, Permission::ACTION_UPDATE]));
    }

    public function delete(User $user): bool
    {
        return $user->hasPermissionTo(Permission::buildPermissionName([Permission::RESOURCE_CONSIGNMENTS, Permission::ACTION_DELETE]));
    }
}
