<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConsignmentRegisterPositions extends Migration
{        //TODO работа на сегодня

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('consignment_register_positions', function (Blueprint $table) {
            $table->id();
            $table->uuid('position_id');
            $table->unsignedBigInteger('consignment_register_id');
            $table->unsignedBigInteger('consignment_id');
            $table->unsignedBigInteger('nomenclature_id');
            $table->unsignedDouble('count');
            $table->unsignedDouble('vat_rate');
            $table->enum('result_status', ['Соответствует', 'Не соответствует'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('consignment_register_positions');
    }
}
