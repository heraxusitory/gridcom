<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
//            $table->boolean('is_external');
            $table->uuid('uuid')->unique();
            $table->string('number'); //генерится автоматически
//            $table->unique(['uuid', 'number'], 'orders_uuid_number_unique');
            $table->timestamp('order_date'); //генерится автоматически
            $table->timestamp('deadline_date');
            $table->enum('customer_status', ['Согласовано', 'Не согласовано', 'На рассмотрении', 'Отклонено', 'Черновик']);
            $table->enum('provider_status', ['Согласовано', 'Не согласовано', 'Согласовано частично', 'На рассмотрении', 'Отклонено', 'Черновик']);
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('provider_id'); //cправочник или вручную
            $table->unsignedBigInteger('contractor_id'); //справочник или пишем сами
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
        Schema::dropIfExists('orders');
    }
}
