<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActualProviderOrderPositions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('actual_provider_order_positions', function (Blueprint $table) {
            $table->id();
            $table->uuid('position_id')->unique();
            $table->unsignedBigInteger('provider_order_id');
            $table->unsignedBigInteger('nomenclature_id');
            $table->unsignedDouble('count');
            $table->unsignedDouble('price_without_vat');
            $table->unsignedDouble('amount_without_vat');
            $table->unsignedDouble('vat_rate');
            $table->unsignedDouble('amount_with_vat');
            $table->timestamp('delivery_time');
            $table->string('delivery_address');
            $table->text('organization_comment');
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
        Schema::dropIfExists('actual_provider_order_positions');
    }
}