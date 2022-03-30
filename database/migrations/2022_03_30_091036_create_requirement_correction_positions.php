<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequirementCorrectionPositions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('requirement_correction_positions', function (Blueprint $table) {
            $table->id();
            $table->uuid('position_id');
            $table->unsignedBigInteger('requirement_correction_id');
            $table->string('status');
            $table->unsignedBigInteger('nomenclature_id');
            $table->unsignedDouble('count');
            $table->unsignedDouble('amount_without_vat');
            $table->unsignedDouble('vat_rate');
            $table->unsignedDouble('amount_with_vat');
            $table->timestamp('delivery_time');
            $table->string('delivery_address');
            $table->text('organization_comment')->nullable();
            $table->text('provider_comment')->nullable();
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
        Schema::dropIfExists('requirement_correction_positions');
    }
}
