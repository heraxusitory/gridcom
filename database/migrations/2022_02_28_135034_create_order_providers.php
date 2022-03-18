<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderProviders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_providers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_contract_id');
//            $table->unsignedBigInteger('contact_id');
            $table->unsignedBigInteger('contr_agent_id');
            $table->string('full_name');
            $table->string('email')->nullable();
            $table->string('phone');
            $table->text('rejected_comment')->nullable();
            $table->text('agreed_comment')->nullable();
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
        Schema::dropIfExists('order_providers');
    }
}
