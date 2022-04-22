<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderCorrectionPositions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_correction_positions', function (Blueprint $table) {
            $table->id();
            $table->uuid('position_id')->unique();
            $table->unsignedBigInteger('order_correction_id');
            $table->unsignedBigInteger('nomenclature_id');
            $table->unsignedDouble('count')->nullable();
            $table->unsignedDouble('amount_without_vat')->nullable();
            $table->unsignedDouble('vat_rate')->nullable();
            $table->unsignedDouble('amount_with_vat')->nullable();
            $table->timestamp('delivery_time')->nullable();
            $table->string('delivery_address')->nullable();
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
        Schema::dropIfExists('order_correction_positions');
    }
}
