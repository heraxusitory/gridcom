<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePriceNegotiationPositionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('price_negotiation_positions', function (Blueprint $table) {
            $table->id();
            $table->uuid('position_id')->unique();
            $table->unsignedBigInteger('price_negotiation_id');
            $table->unsignedBigInteger('nomenclature_id');
            $table->unsignedDouble('new_price_without_vat')->nullable();
            $table->unsignedDouble('agreed_price')->nullable();
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
        Schema::dropIfExists('price_negotiation_positions');
    }
}
