<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeTypeColumnsInConsignmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('consignments', function (Blueprint $table) {
            $table->string('number')->nullable()->change();
            $table->unsignedBigInteger('organization_id')->nullable()->change();
            $table->unsignedBigInteger('provider_contr_agent_id')->nullable()->change();
            $table->unsignedBigInteger('provider_contract_id')->nullable()->change();
            $table->unsignedBigInteger('contractor_contr_agent_id')->nullable()->change();
            $table->unsignedBigInteger('work_agreement_id')->nullable()->change();
            $table->unsignedBigInteger('customer_object_id')->nullable()->change();
            $table->unsignedBigInteger('customer_sub_object_id')->nullable()->change();
            DB::statement("ALTER TABLE consignments ALTER COLUMN date DROP NOT NULL");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('consignments', function (Blueprint $table) {
            //
        });
    }
}
