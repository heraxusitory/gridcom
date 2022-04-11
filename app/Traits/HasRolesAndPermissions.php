<?php


namespace App\Traits;


use App\Models\Permission;
use App\Models\Role;

trait HasRolesAndPermissions
{
//    /**
//     * @return mixed
//     */
//    public function roles()
//    {
//        return $this->belongsToMany(Role::class, 'users_roles');
//    }

//    /**
//     * @return mixed
//     */
//    public function permissions()
//    {
//        return $this->belongsToMany(Permission::class, 'users_permissions');
//    }

    public function hasRole(...$roles)
    {
        foreach ($roles as $role) {
            if ($this->roles->contains($role)) {
                return true;
            }
        }
        return false;
    }

    public function isAdmin()
    {
        return $this->hasRole(Role::ADMIN());
    }

    public function isMethodologist()
    {
        return $this->hasRole(Role::METHODOLOGIST());

    }

    public function isProvider()
    {
        return $this->hasRole(Role::PROVIDER());
    }

    public function isContractor()
    {
        return $this->hasRole(Role::CONTRACTOR());
    }

//    /**
//     * @param $permission
//     * @return bool
//     */
//    public function hasPermission($permission)
//    {
//        return (bool)$this->permissions->where('slug', $permission)->count();
//    }

    /**
     * @param $permission
     * @return bool
     */
    public function hasPermissionTo(string $permission)
    {
        return $this->hasPermissionThroughRole($permission)/* || $this->hasPermission($permission->slug)*/ ;
    }

    /**
     * @param $permission
     * @return bool
     */
    public function hasPermissionThroughRole(string $permission)
    {
        $permission = $this->getPermission($permission);
        if (!is_null($permission)) {
            foreach ($permission->roles as $role) {
                if ($this->roles->contains($role->slug)) {
                    return true;
                }
            }

            /*Возможно пригодится в будущем, но это не точно*/
//            foreach ($this->roles as $role) {
            /* $roles = Role::whereIn('slug', $this->roles)->get();
             if ($roles->isNotEmpty()) {
 //                dd($permission->roles);
                 foreach ($roles as $role) {
                     if ($role->permissions->contains($permission)) {
                         return true;
                     }
                 }
             }*/
//            }
        }
        return false;
    }

    /**
     * @param array $permissions
     * @return mixed
     */
    public function getAllPermissions(array $permissions)
    {
        return Permission::query()->whereIn('slug', $permissions)->get();
    }

    public function getPermission(string $permission)
    {
        return Permission::query()->where('slug', $permission)->first();
    }

    /**
     * @param mixed ...$permissions
     * @return $this
     */
    public function givePermissionsTo(...$permissions)
    {
        $permissions = $this->getAllPermissions($permissions);
        if ($permissions === null) {
            return $this;
        }
        $this->permissions()->saveMany($permissions);
        return $this;
    }

    /**
     * @param mixed ...$permissions
     * @return $this
     */
    public function deletePermissions(...$permissions)
    {
        $permissions = $this->getAllPermissions($permissions);
        $this->permissions()->detach($permissions);
        return $this;
    }

    /**
     * @param mixed ...$permissions
     * @return HasRolesAndPermissions
     */
    public function refreshPermissions(...$permissions)
    {
        $this->permissions()->detach();
        return $this->givePermissionsTo($permissions);
    }
}
