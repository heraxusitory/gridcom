<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ConsignmentPositions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('consignment_positions', function (Blueprint $table) {
            $table->id();
            $table->uuid('position_id');
            $table->unsignedBigInteger('consignment_id');
            $table->unsignedBigInteger('nomenclature_id');
            $table->unsignedBigInteger('unit_id');
            $table->unsignedDouble('count');
            $table->unsignedDouble('price_without_vat');
            $table->unsignedDouble('amount_without_vat');
            $table->unsignedDouble('vat_rate');
            $table->unsignedDouble('amount_with_vat');
            $table->string('country');
            $table->string('cargo_custom_declaration');
            $table->string('declaration');
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
        Schema::dropIfExists('consignment_note_positions');
    }
}
