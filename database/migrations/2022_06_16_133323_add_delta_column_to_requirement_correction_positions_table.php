<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeltaColumnToRequirementCorrectionPositionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('requirement_correction_positions', function (Blueprint $table) {
            $table->unsignedDouble('delta')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('requirement_correction_positions', function (Blueprint $table) {
            $table->dropColumn('delta');
        });
    }
}
