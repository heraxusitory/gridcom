<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewColumnsInProviderOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('provider_orders', function (Blueprint $table) {
            $table->unsignedDouble('common_amount_without_vat_in_base_positions')->nullable();
            $table->unsignedDouble('common_amount_with_vat_in_base_positions')->nullable();
            $table->unsignedDouble('common_amount_without_vat_in_actual_positions')->nullable();
            $table->unsignedDouble('common_amount_with_vat_in_actual_positions')->nullable();
        });
        Schema::table('requirement_corrections', function (Blueprint $table) {
            $table->unsignedDouble('common_amount_without_vat')->nullable();
            $table->unsignedDouble('common_amount_with_vat')->nullable();
        });
        Schema::table('order_corrections', function (Blueprint $table) {
            $table->unsignedDouble('common_amount_without_vat')->nullable();
            $table->unsignedDouble('common_amount_with_vat')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('provider_orders', function (Blueprint $table) {
            $table->dropColumn('common_amount_without_vat_in_base_positions');
            $table->dropColumn('common_amount_with_vat_in_base_positions');
            $table->dropColumn('common_amount_without_vat_in_actual_positions');
            $table->dropColumn('common_amount_with_vat_in_actual_positions');
        });
        Schema::table('requirement_corrections', function (Blueprint $table) {
            $table->dropColumn('common_amount_without_vat');
            $table->dropColumn('common_amount_with_vat');
        });
        Schema::table('order_corrections', function (Blueprint $table) {
            $table->dropColumn('common_amount_without_vat');
            $table->dropColumn('common_amount_with_vat');
        });
    }
}
