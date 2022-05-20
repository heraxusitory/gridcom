<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToConsignmentRegisterPositionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('consignment_register_positions', function (Blueprint $table) {
            $table->unsignedDouble('price_without_vat');
            $table->unsignedDouble('amount_without_vat');
            $table->unsignedDouble('amount_with_vat');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('consignment_register_positions', function (Blueprint $table) {
            $table->dropColumn('price_without_vat');
            $table->dropColumn('amount_without_vat');
            $table->dropColumn('amount_with_vat');
        });
    }
}
