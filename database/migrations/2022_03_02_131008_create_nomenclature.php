<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNomenclature extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nomenclature', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('mnemocode')->unique()->nullable();
            $table->string('name')->nullable();
            $table->unsignedDouble('price')->nullable();
            $table->boolean('is_visible_to_client')->default('false');
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
        Schema::dropIfExists('nomenclature');
    }
}
