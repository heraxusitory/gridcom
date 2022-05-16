<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChaneColumnsInConsignmentRegisterPositionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('consignment_register_positions', function (Blueprint $table) {
            $table->unsignedBigInteger('nomenclature_id')->nullable()->change();
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
            $table->unsignedBigInteger('nomenclature_id')->nullable(false)->change();
        });
    }
}
