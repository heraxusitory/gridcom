<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToPriceNegotiationPositionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('price_negotiation_positions', function (Blueprint $table) {
            $table->unsignedDouble('current_price_without_vat')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('price_negotiation_positions', function (Blueprint $table) {
            $table->dropColumn('current_price_without_vat');
        });
    }
}
