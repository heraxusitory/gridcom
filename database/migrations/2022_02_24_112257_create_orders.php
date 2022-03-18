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
            $table->uuid('uuid')->index();
            $table->string('number'); //генерится автоматически
            $table->unique(['uuid', 'number'], 'orders_uuid_number_unique');
            $table->timestamp('order_date'); //генерится автоматически
//            $table->timestamp('approval_date');
            $table->timestamp('deadline_date');
            $table->enum('customer_status', ['Согласовано', 'Не согласовано', 'На рассмотрении', 'Отклонено', 'Черновик']);
            $table->enum('provider_status', ['Согласовано', 'Не согласовано', 'Согласовано частично', 'На рассмотрении', 'Отклонено', 'Черновик']);
            $table->unsignedBigInteger('customer_id');
//            $table->string('work_agreement'); //справочник
//            $table->timestamp('work_agreement_date'); //автоматически подставлять из справочника по work_agreement_id
//            $table->enum('work_type', ['Разработка', 'Интеграция', 'Строительство']);
//            $table->string('object');
//            $table->string('sub_object');
//            $table->timestamp('work_start_date');
//            $table->timestamp('work_end_date');

            $table->unsignedBigInteger('provider_id'); //cправочник или вручную
//            $table->string('provider_contract');//справочник или вручную
//            $table->timestamp('provider_contract_date');//справочник или вручную, автоматически подставлять из provider_contract
//            $table->string('provider_full_name');
//            $table->string('provider_email');
//            $table->string('provider_phone');

            $table->unsignedBigInteger('contractor_id'); //справочник или пишем сами
//            $table->string('contractor_full_name');
//            $table->string('contractor_email');
//            $table->string('contractor_phone');
//            $table->string('contractor_responsible_full_name');
//            $table->string('contractor_responsible_phone');

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
