<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ConsignmentNotes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('consignment_notes', function (Blueprint $table) {
            $table->id();
            $table->string('number');
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
