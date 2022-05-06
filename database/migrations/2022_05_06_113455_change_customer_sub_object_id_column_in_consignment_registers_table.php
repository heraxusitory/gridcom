<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeCustomerSubObjectIdColumnInConsignmentRegistersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('consignment_registers', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_sub_object_id')->nullable()->change();
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
            $table->unsignedBigInteger('customer_sub_object_id')->nullable(false)->change();
        });
    }
}
