<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Consignments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('consignments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('number');
//            $table->unique(['uuid', 'number'], 'consignments_uuid_number_unique');
            $table->boolean('is_approved')->default(false);
            $table->timestamp('date');
            $table->unsignedBigInteger('order_id');
            $table->string('responsible_full_name');
            $table->string('responsible_phone');
            $table->tinyText('comment');
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
        Schema::dropIfExists('consignment_notes');
    }
}
