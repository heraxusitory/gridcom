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
            $table->timestamp('date')->nullable();
            $table->string('number')->nullable();
            $table->string('provider_status')->nullable();
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
