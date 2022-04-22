<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBaseProviderOrderPositions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('base_provider_order_positions', function (Blueprint $table) {
            $table->id();
            $table->uuid('position_id')->unique();
            $table->unsignedBigInteger('provider_order_id');
            $table->unsignedBigInteger('nomenclature_id');
            $table->unsignedDouble('count')->nullable();
            $table->unsignedDouble('price_without_vat')->nullable();
            $table->unsignedDouble('amount_without_vat')->nullable();
            $table->unsignedDouble('vat_rate')->nullable();
            $table->unsignedDouble('amount_with_vat')->nullable();
            $table->timestamp('delivery_time')->nullable();
            $table->string('delivery_address')->nullable();
            $table->text('organization_comment')->nullable();
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
        Schema::dropIfExists('base_provider_order_positions');
    }
}
