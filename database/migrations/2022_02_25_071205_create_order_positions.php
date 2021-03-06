<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderPositions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_positions', function (Blueprint $table) {
            $table->id();
            $table->uuid('position_id'); //TODO спорный вопрос делать ли это поле и тем более уникальным для обеих сторон
            $table->unsignedBigInteger('order_id');
//            $table->unique(['position_id', 'order_id'], 'op_position_id_order_id_unique');
            $table->string('status');
            $table->unsignedBigInteger('nomenclature_id');
//            $table->unsignedBigInteger('unit_id');
            $table->unsignedDouble('count');
            $table->unsignedDouble('price_without_vat');
            $table->unsignedDouble('amount_without_vat');
            $table->timestamp('delivery_time');
            $table->timestamp('delivery_plan_time')->nullable();
            $table->string('delivery_address')->nullable();//справочник скорее всего, но мб вручную будет
            $table->text('customer_comment')->nullable();
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
        Schema::dropIfExists('mtr_positions');
    }
}
