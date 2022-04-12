<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentRegisters extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_registers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('number')->index();
            $table->string('customer_status');
            $table->string('provider_status');
            $table->unsignedBigInteger('provider_contr_agent_id');
            $table->unsignedBigInteger('contractor_contr_agent_id');
            $table->unsignedBigInteger('provider_contract_id');
            $table->string('responsible_full_name');
            $table->string('responsible_phone');
            $table->string('comment');
            $table->timestamp('date');
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
        Schema::dropIfExists('payment_registers');
    }
}
