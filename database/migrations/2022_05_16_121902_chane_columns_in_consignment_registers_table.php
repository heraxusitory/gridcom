<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChaneColumnsInConsignmentRegistersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('consignment_registers', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->nullable()->change();
            $table->unsignedBigInteger('contractor_contr_agent_id')->nullable()->change();
            $table->unsignedBigInteger('provider_contr_agent_id')->nullable()->change();
            $table->unsignedBigInteger('customer_object_id')->nullable()->change();
            $table->unsignedBigInteger('work_agreement_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('consignment_registers', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->nullable(false)->change();
            $table->unsignedBigInteger('contractor_contr_agent_id')->nullable(false)->change();
            $table->unsignedBigInteger('provider_contr_agent_id')->nullable(false)->change();
            $table->unsignedBigInteger('customer_object_id')->nullable(false)->change();
            $table->unsignedBigInteger('work_agreement_id')->nullable(false)->change();
        });
    }
}
