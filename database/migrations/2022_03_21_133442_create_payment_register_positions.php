<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentRegisterPositions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_register_positions', function (Blueprint $table) {
            $table->id();
            $table->uuid('position_id');
            $table->unsignedBigInteger('payment_register_id');
            $table->unsignedBigInteger('order_id');
            $table->string('payment_order_number');
            $table->timestamp('payment_order_date');
            $table->unsignedDouble('amount_payment');
            $table->enum('payment_type', ['Аванс', 'Постоплата']);
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
        Schema::dropIfExists('payment_register_positions');
    }
}
