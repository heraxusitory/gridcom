<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRolesPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roles_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('permission_id');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
            $table->primary(['role_id', 'permission_id']);
        });

        $contractor_permission_ids = Permission::query()->whereIn('slug', [
            Permission::RESOURCE_ORDERS . '.' . Permission::ACTION_VIEW,
            Permission::RESOURCE_ORDERS . '.' . Permission::ACTION_CREATE,
            Permission::RESOURCE_ORDERS . '.' . Permission::ACTION_UPDATE,
            Permission::RESOURCE_ORDERS . '.' . Permission::ACTION_DELETE,

            Permission::RESOURCE_PAYMENT_REGISTERS . '.' . Permission::ACTION_VIEW,
            Permission::RESOURCE_PAYMENT_REGISTERS . '.' . Permission::ACTION_CREATE,
            Permission::RESOURCE_PAYMENT_REGISTERS . '.' . Permission::ACTION_UPDATE,

            Permission::RESOURCE_CONSIGNMENTS . '.' . Permission::ACTION_VIEW,
            Permission::RESOURCE_CONSIGNMENTS . '.' . Permission::ACTION_CREATE,
            Permission::RESOURCE_CONSIGNMENTS . '.' . Permission::ACTION_UPDATE,

            Permission::RESOURCE_CONSIGNMENT_REGISTERS . '.' . Permission::ACTION_VIEW,
            Permission::RESOURCE_CONSIGNMENT_REGISTERS . '.' . Permission::ACTION_CREATE,
            Permission::RESOURCE_CONSIGNMENT_REGISTERS . '.' . Permission::ACTION_UPDATE,

            Permission::RESOURCE_CONTRACTOR_NOTIFICATIONS . '.' . Permission::ACTION_VIEW,
            Permission::RESOURCE_CONTRACTOR_NOTIFICATIONS . '.' . Permission::ACTION_UPDATE,

            Permission::RESOURCE_PRICE_NEGOTIATIONS . '.' . Permission::ACTION_VIEW,
            Permission::RESOURCE_PRICE_NEGOTIATIONS . '.' . Permission::ACTION_CREATE,
            Permission::RESOURCE_PRICE_NEGOTIATIONS . '.' . Permission::ACTION_UPDATE,

            Permission::RESOURCE_REQUEST_ADDITION_OBJECTS . '.' . Permission::ACTION_VIEW,
            Permission::RESOURCE_REQUEST_ADDITION_OBJECTS . '.' . Permission::ACTION_CREATE,
            Permission::RESOURCE_REQUEST_ADDITION_OBJECTS . '.' . Permission::ACTION_UPDATE,

            Permission::RESOURCE_REQUEST_ADDITION_NOMENCLATURES . '.' . Permission::ACTION_VIEW,
            Permission::RESOURCE_REQUEST_ADDITION_NOMENCLATURES . '.' . Permission::ACTION_CREATE,
            Permission::RESOURCE_REQUEST_ADDITION_NOMENCLATURES . '.' . Permission::ACTION_UPDATE,

            Permission::RESOURCE_REFERENCE_CONTR_AGENTS . '.' . Permission::ACTION_VIEW,
            Permission::RESOURCE_REFERENCE_OBJECTS . '.' . Permission::ACTION_VIEW,
            Permission::RESOURCE_REFERENCE_ORGANIZATIONS . '.' . Permission::ACTION_VIEW,
            Permission::RESOURCE_REFERENCE_PROVIDER_CONTRACTS . '.' . Permission::ACTION_VIEW,
            Permission::RESOURCE_REFERENCE_WORK_AGREEMENTS . '.' . Permission::ACTION_VIEW,
            Permission::RESOURCE_REFERENCE_NOMENCLATURE . '.' . Permission::ACTION_VIEW,
        ])->pluck('id');

        $provider_permission_ids = Permission::query()->whereIn('slug', [
            Permission::RESOURCE_ORDERS . '.' . Permission::ACTION_VIEW,
            Permission::RESOURCE_ORDERS . '.' . Permission::ACTION_UPDATE,

            Permission::RESOURCE_PROVIDER_ORDERS . '.' . Permission::ACTION_VIEW,
            Permission::RESOURCE_PROVIDER_ORDERS . '.' . Permission::ACTION_UPDATE,

            Permission::RESOURCE_PAYMENT_REGISTERS . '.' . Permission::ACTION_VIEW,
            Permission::RESOURCE_PAYMENT_REGISTERS . '.' . Permission::ACTION_UPDATE,

            Permission::RESOURCE_CONSIGNMENTS . '.' . Permission::ACTION_VIEW,
            Permission::RESOURCE_CONSIGNMENTS . '.' . Permission::ACTION_CREATE,
            Permission::RESOURCE_CONSIGNMENTS . '.' . Permission::ACTION_UPDATE,

            Permission::RESOURCE_CONSIGNMENT_REGISTERS . '.' . Permission::ACTION_VIEW,
            Permission::RESOURCE_CONSIGNMENT_REGISTERS . '.' . Permission::ACTION_CREATE,
            Permission::RESOURCE_CONSIGNMENT_REGISTERS . '.' . Permission::ACTION_UPDATE,

            Permission::RESOURCE_CONTRACTOR_NOTIFICATIONS . '.' . Permission::ACTION_VIEW,
            Permission::RESOURCE_CONTRACTOR_NOTIFICATIONS . '.' . Permission::ACTION_UPDATE,

            Permission::RESOURCE_ORGANIZATION_NOTIFICATIONS . '.' . Permission::ACTION_VIEW,
            Permission::RESOURCE_ORGANIZATION_NOTIFICATIONS . '.' . Permission::ACTION_CREATE,
            Permission::RESOURCE_ORGANIZATION_NOTIFICATIONS . '.' . Permission::ACTION_UPDATE,

            Permission::RESOURCE_PRICE_NEGOTIATIONS . '.' . Permission::ACTION_VIEW,
            Permission::RESOURCE_PRICE_NEGOTIATIONS . '.' . Permission::ACTION_CREATE,
            Permission::RESOURCE_PRICE_NEGOTIATIONS . '.' . Permission::ACTION_UPDATE,

            Permission::RESOURCE_REQUEST_ADDITION_OBJECTS . '.' . Permission::ACTION_VIEW,
            Permission::RESOURCE_REQUEST_ADDITION_OBJECTS . '.' . Permission::ACTION_CREATE,
            Permission::RESOURCE_REQUEST_ADDITION_OBJECTS . '.' . Permission::ACTION_UPDATE,

            Permission::RESOURCE_REQUEST_ADDITION_NOMENCLATURES . '.' . Permission::ACTION_VIEW,
            Permission::RESOURCE_REQUEST_ADDITION_NOMENCLATURES . '.' . Permission::ACTION_CREATE,
            Permission::RESOURCE_REQUEST_ADDITION_NOMENCLATURES . '.' . Permission::ACTION_UPDATE,

            Permission::RESOURCE_REFERENCE_CONTR_AGENTS . '.' . Permission::ACTION_VIEW,
            Permission::RESOURCE_REFERENCE_OBJECTS . '.' . Permission::ACTION_VIEW,
            Permission::RESOURCE_REFERENCE_ORGANIZATIONS . '.' . Permission::ACTION_VIEW,
            Permission::RESOURCE_REFERENCE_PROVIDER_CONTRACTS . '.' . Permission::ACTION_VIEW,
            Permission::RESOURCE_REFERENCE_WORK_AGREEMENTS . '.' . Permission::ACTION_VIEW,
            Permission::RESOURCE_REFERENCE_NOMENCLATURE . '.' . Permission::ACTION_VIEW,
        ])->pluck('id');

        $contractor_role = Role::query()->where('slug', Role::CONTRACTOR())->first();
        $contractor_role->permissions()->sync($contractor_permission_ids);
        $provider_role = Role::query()->where('slug', Role::PROVIDER())->first();
        $provider_role->permissions()->sync($provider_permission_ids);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roles_permissions');
    }
}
