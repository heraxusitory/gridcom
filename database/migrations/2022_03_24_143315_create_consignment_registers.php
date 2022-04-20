<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConsignmentRegisters extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //TODO работа на сегодня
        Schema::create('consignment_registers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('number')->index();
            $table->string('customer_status');
            $table->string('contr_agent_status');
            $table->boolean('is_approved')->default(false);
//            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('organization_id');
            $table->unsignedBigInteger('contractor_contr_agent_id');
            $table->unsignedBigInteger('provider_contr_agent_id');
            $table->unsignedBigInteger('customer_object_id');
            $table->unsignedBigInteger('customer_sub_object_id');
            $table->unsignedBigInteger('work_agreement_id');
            $table->string('responsible_full_name')->nullable();
            $table->string('responsible_phone')->nullable();
            $table->text('comment')->nullable();
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
        Schema::dropIfExists('consignment_registers');
    }
}
