<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContractorNotifications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contractor_notifications', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->timestamp('date');
            $table->string('status');
            $table->unsignedBigInteger('contractor_contr_agent_id');
            $table->unsignedBigInteger('provider_contr_agent_id');
            $table->unsignedBigInteger('provider_contract_id');
            $table->timestamp('date_fact_delivery');
            $table->string('delivery_address');
            $table->string('car_info');
            $table->string('driver_phone');
            $table->string('responsible_full_name');
            $table->string('responsible_phone');
            $table->text('contractor_comment')->nullable();
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
        Schema::dropIfExists('contractor_notifications');
    }
}
