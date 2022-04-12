<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContractorNotificationPositions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contractor_notification_positions', function (Blueprint $table) {
            $table->id();
            $table->uuid('position_id')->unique();
            $table->unsignedBigInteger('contractor_notification_id');
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('nomenclature_id');
            $table->unsignedDouble('count');
            $table->unsignedDouble('vat_rate');
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
        Schema::dropIfExists('contractor_notification_positions');
    }
}
