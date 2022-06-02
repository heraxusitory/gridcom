<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNomenclatureUnitToRequestAdditionNomenclaturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('request_addition_nomenclatures', function (Blueprint $table) {
            $table->string('nomenclature_unit')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('request_addition_nomenclatures', function (Blueprint $table) {
            $table->dropColumn('nomenclature_unit');
        });
    }
}
