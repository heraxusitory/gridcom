<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMtrPositions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mtr_positions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_info_id');
            $table->string('mnemocode');
            $table->string('nomenclature');
            $table->enum('unit', ['шт.', 'кг.', 'л.']);
            $table->unsignedDouble('count');
            $table->unsignedDouble('price_without_vat');
            $table->unsignedDouble('amount_without_vat');
            $table->unsignedDouble('total_amount');
            $table->timestamp('delivery time');
            $table->string('delivery_address');//справочник скорее всего, но мб вручную будет
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
        Schema::dropIfExists('mtr_positions');
    }
}
