<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToRequestAdditionTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('request_addition_nomenclatures', function (Blueprint $table) {
            $table->string('nomenclature_name')->nullable();
            $table->string('type');
            $table->unsignedBigInteger('nomenclature_id')->nullable()->change();
        });
        Schema::table('request_addition_objects', function (Blueprint $table) {
            $table->string('object_name')->nullable();
            $table->string('type');
            $table->unsignedBigInteger('object_id')->nullable()->change();
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
            $table->dropColumn('nomenclature_name')->nullable();
            $table->dropColumn('type');
            $table->unsignedBigInteger('nomenclature_id')->nullable(false)->change();
        });
        Schema::table('request_addition_objects', function (Blueprint $table) {
            $table->dropColumn('object_name')->nullable();
            $table->dropColumn('type');
            $table->unsignedBigInteger('object_id')->nullable(false)->change();
        });
    }
}
