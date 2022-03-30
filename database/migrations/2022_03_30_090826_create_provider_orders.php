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
            $table->uuid('uuid');
            $table->string('number');
            $table->timestamp('order_date');
            $table->string('contract_number');
            $table->timestamp('contract_date');
            $table->enum('contract_stage', [1, 2, 3, 4, 5, 6, 7]);
            $table->unsignedBigInteger('provider_contr_agent_id');
            $table->unsignedBigInteger('organization_id');
            $table->string('responsible_full_name');
            $table->string('responsible_phone');
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
