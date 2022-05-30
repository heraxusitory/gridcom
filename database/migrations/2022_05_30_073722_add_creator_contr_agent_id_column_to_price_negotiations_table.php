<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreatorContrAgentIdColumnToPriceNegotiationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('price_negotiations', function (Blueprint $table) {
            $table->unsignedBigInteger('creator_contr_agent_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('price_negotiations', function (Blueprint $table) {
            $table->dropColumn('creator_contr_agent_id');
        });
    }
}
