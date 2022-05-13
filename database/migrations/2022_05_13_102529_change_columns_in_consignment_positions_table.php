<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnsInConsignmentPositionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('consignment_positions', function (Blueprint $table) {
            $table->string('cargo_custom_declaration')->nullable()->change();
            $table->string('declaration')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('consignment_positions', function (Blueprint $table) {
            $table->string('cargo_custom_declaration')->nullable(false)->change();
            $table->string('declaration')->nullable(false)->change();
        });
    }
}
