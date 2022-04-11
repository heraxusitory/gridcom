<?php

namespace App\Policies\References;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class WorkAgreementPolicy
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
        return $user->hasPermissionTo(Permission::buildPermissionName([Permission::RESOURCE_REFERENCE_WORK_AGREEMENTS, Permission::ACTION_VIEW]));
    }
}
