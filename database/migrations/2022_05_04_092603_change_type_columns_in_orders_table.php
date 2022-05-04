<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeTypeColumnsInOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('number')->nullable()->change();
//            $table->timestamp('order_date')->nullable()->change();
//            $table->timestamp('deadline_date')->nullable()->change();
            $table->string('customer_status')->nullable()->change();
            $table->string('provider_status')->nullable()->change();
            $table->unsignedBigInteger('customer_id')->nullable()->change();
            $table->unsignedBigInteger('provider_id')->nullable()->change();
            $table->unsignedBigInteger('contractor_id')->nullable()->change();

            DB::statement("ALTER TABLE orders ALTER COLUMN order_date DROP NOT NULL");
            DB::statement("ALTER TABLE orders ALTER COLUMN deadline_date DROP NOT NULL");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
        });

    }
}
