<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderContractors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_contractors', function (Blueprint $table) {
            $table->id();
//            $table->unsignedBigInteger('contact_id');
            $table->unsignedBigInteger('contr_agent_id');
            $table->string('full_name');
            $table->string('email')->nullable();
            $table->string('phone');
            $table->string('contractor_responsible_full_name');
            $table->string('contractor_responsible_phone');
            $table->text('comment')->nullable();
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
        Schema::dropIfExists('order_contractors');
    }
}
