<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeColumnsInOrganizationNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('organization_notifications', function (Blueprint $table) {

            $table->string('delivery_address')->nullable()->change();
            $table->string('car_info')->nullable()->change();
            $table->string('driver_phone')->nullable()->change();
            $table->string('responsible_full_name')->nullable()->change();
            $table->string('responsible_phone')->nullable()->change();
            DB::statement("ALTER TABLE organization_notifications ALTER COLUMN date DROP NOT NULL");
            DB::statement("ALTER TABLE organization_notifications ALTER COLUMN date_fact_delivery DROP NOT NULL");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('organization_notifications', function (Blueprint $table) {
            $table->string('delivery_address')->nullable(false)->change();
            $table->string('car_info')->nullable(false)->change();
            $table->string('driver_phone')->nullable(false)->change();
            $table->string('responsible_full_name')->nullable(false)->change();
            $table->string('responsible_phone')->nullable(false)->change();
            DB::statement("ALTER TABLE organization_notifications ALTER COLUMN date SET NOT NULL");
            DB::statement("ALTER TABLE organization_notifications ALTER COLUMN date_fact_delivery SET NOT NULL");
        });
    }
}
