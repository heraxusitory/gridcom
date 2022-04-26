<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnSyncRequiredToTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('sync_required')->default(false);
        });
        Schema::table('consignment_registers', function (Blueprint $table) {
            $table->boolean('sync_required')->default(false);
        });
        Schema::table('consignments', function (Blueprint $table) {
            $table->boolean('sync_required')->default(false);
        });
        Schema::table('organization_notifications', function (Blueprint $table) {
            $table->boolean('sync_required')->default(false);
        });
        Schema::table('payment_registers', function (Blueprint $table) {
            $table->boolean('sync_required')->default(false);
        });
        Schema::table('price_negotiations', function (Blueprint $table) {
            $table->boolean('sync_required')->default(false);
        });
        Schema::table('provider_orders', function (Blueprint $table) {
            $table->boolean('sync_required')->default(false);
        });
        Schema::table('request_addition_nomenclatures', function (Blueprint $table) {
            $table->boolean('sync_required')->default(false);
        });
        Schema::table('request_addition_objects', function (Blueprint $table) {
            $table->boolean('sync_required')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tables', function (Blueprint $table) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('sync_required');
            });
            Schema::table('consignment_registers', function (Blueprint $table) {
                $table->dropColumn('sync_required');
            });
            Schema::table('consignments', function (Blueprint $table) {
                $table->dropColumn('sync_required');
            });
            Schema::table('organization_notifications', function (Blueprint $table) {
                $table->dropColumn('sync_required');
            });
            Schema::table('payment_registers', function (Blueprint $table) {
                $table->dropColumn('sync_required');
            });
            Schema::table('price_negotiations', function (Blueprint $table) {
                $table->dropColumn('sync_required');
            });
            Schema::table('provider_orders', function (Blueprint $table) {
                $table->dropColumn('sync_required');
            });
            Schema::table('request_addition_nomenclatures', function (Blueprint $table) {
                $table->dropColumn('sync_required');
            });
            Schema::table('request_addition_objects', function (Blueprint $table) {
                $table->dropColumn('sync_required');
            });
        });
    }
}
