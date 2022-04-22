<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestAdditionObjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_addition_objects', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('number')->nullable();
            $table->timestamp('date')->nullable();
            $table->unsignedBigInteger('contr_agent_id');
            $table->unsignedBigInteger('work_agreement_id')->nullable();
            $table->unsignedBigInteger('provider_contract_id')->nullable();
            $table->unsignedBigInteger('organization_id');
            $table->string('organization_status');
            $table->unsignedBigInteger('object_id');
            $table->text('description')->nullable();
            $table->string('responsible_full_name')->nullable();
            $table->text('contr_agent_comment')->nullable();
            $table->text('organization_comment')->nullable();
            $table->string('file_url')->nullable();
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
        Schema::dropIfExists('request_addition_objects');
    }
}
