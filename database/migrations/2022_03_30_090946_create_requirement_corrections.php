<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequirementCorrections extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('requirement_corrections', function (Blueprint $table) {
            $table->id();
            $table->uuid('correction_id')->unique();
            $table->unsignedBigInteger('provider_order_id');
            $table->timestamp('date');
            $table->string('number');
            $table->string('provider_status');
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
        Schema::dropIfExists('requirement_corrections');
    }
}
