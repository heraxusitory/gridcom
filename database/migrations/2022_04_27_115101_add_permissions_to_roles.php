<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPermissionsToRoles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('roles_permissions', function (Blueprint $table) {

            $provider_permission_ids = Permission::query()->whereIn('slug', [
                Permission::RESOURCE_ORGANIZATION_NOTIFICATIONS . '.' . Permission::ACTION_CREATE,
            ])->pluck('id');

            $provider_role = Role::query()->where('slug', Role::PROVIDER())->first();
            $provider_role->permissions()->sync($provider_permission_ids);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('roles_permissions', function (Blueprint $table) {
            $provider_permission_ids = Permission::query()->whereIn('slug', [
                Permission::RESOURCE_ORGANIZATION_NOTIFICATIONS . '.' . Permission::ACTION_CREATE,
            ])->pluck('id');

            $provider_role = Role::query()->where('slug', Role::PROVIDER())->first();
            $provider_role->permissions()->detach($provider_permission_ids);
        });
    }
}
