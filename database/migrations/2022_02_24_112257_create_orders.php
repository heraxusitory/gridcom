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
            $table->uuid('id')->primary();
            $table->boolean('is_external');
            $table->string('number')->unique(); //генерится автоматически
            $table->timestamp('order_date'); //генерится автоматически
//            $table->timestamp('approval_date');
            $table->timestamp('deadline_date');
            $table->enum('customer_status', ['Согласовано', 'На рассмотрении', 'Отклонено']);
            $table->enum('provider_status', ['Согласовано', 'Согласовано частично', 'На рассмотрении', 'Отклонено']);
            $table->string('customer_filial_branch');
            $table->string('work_agreement'); //справочник
            $table->timestamp('work_agreement_date'); //автоматически подставлять из справочника по work_agreement_id
            $table->enum('work_type', ['Разработка', 'Интеграция', 'Строительство']);
            $table->string('object');
            $table->string('sub_object');
            $table->string('provider'); //cправочник или вручную
            $table->string('provider_contract');//справочник или вручную
            $table->timestamp('provider_contract_date');//справочник или вручную, автоматически подставлять из provider_contract
            $table->string('provider_full_name');
            $table->string('provider_email');
            $table->string('provider_phone');

            $table->string('contractor'); //справочник или пишем сами
            $table->string('contractor_full_name');
            $table->string('contractor_email');
            $table->string('contractor_phone');
            $table->string('contractor_responsible_full_name');
            $table->string('contractor_responsible_phone');

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
