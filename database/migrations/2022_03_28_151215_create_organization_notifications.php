<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrganizationNotifications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('organization_notifications', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->index();
            $table->timestamp('date');
            $table->string('status');
            $table->string('stage');
            $table->unsignedBigInteger('contractor_contr_agent_id');
            $table->unsignedBigInteger('provider_contr_agent_id');
            $table->unsignedBigInteger('work_agreement_id');
            $table->timestamp('date_fact_delivery');
            $table->string('delivery_address');
            $table->string('car_info');
            $table->string('driver_phone');
            $table->string('responsible_full_name');
            $table->string('responsible_phone');
            $table->text('comment');
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
        Schema::dropIfExists('organization_notifications');
    }
}
