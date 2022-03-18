<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ConsignmentNotePositions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('consignment_note_positions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('consignment_note_id');
            $table->unsignedBigInteger('nomenclature_id');
            $table->unsignedBigInteger('unit_id');
            $table->unsignedDouble('count');
            $table->unsignedDouble('price_without_vat');
            $table->unsignedDouble('amount_without_vat');
            $table->unsignedInteger('vat_rate');
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
