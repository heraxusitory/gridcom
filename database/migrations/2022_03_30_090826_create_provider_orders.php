<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProviderOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provider_orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('number')->nullable();
            $table->timestamp('order_date')->nullable();
            $table->string('contract_number')->nullable();
            $table->timestamp('contract_date')->nullable();
            $table->string('contract_stage')->nullable();
            $table->unsignedBigInteger('provider_contr_agent_id')->nullable();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->string('responsible_full_name')->nullable();
            $table->string('responsible_phone')->nullable();
            $table->string('organization_comment')->nullable();
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
        Schema::dropIfExists('provider_orders');
    }
}
