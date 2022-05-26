<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddedColumnToOrganizationNotificationPositionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('organization_notification_positions', function (Blueprint $table) {
            $table->unsignedDouble('price_without_vat')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('organization_notification_positions', function (Blueprint $table) {
            $table->unsignedDouble('price_without_vat')->nullable();
        });
    }
}
