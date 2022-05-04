<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeTypeColumnsInCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_customers', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->nullable()->change();
            $table->unsignedBigInteger('work_agreement_id')->nullable()->change();
            $table->unsignedBigInteger('object_id')->nullable()->change();
            DB::statement("ALTER TABLE order_customers ALTER COLUMN work_start_date DROP NOT NULL");
            DB::statement("ALTER TABLE order_customers ALTER COLUMN work_end_date DROP NOT NULL");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_customers', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->nullable(false)->change();
            $table->unsignedBigInteger('work_agreement_id')->nullable(false)->change();
            $table->unsignedBigInteger('object_id')->nullable(false)->change();
            DB::statement("ALTER TABLE order_customers ALTER COLUMN work_start_date SET NOT NULL");
            DB::statement("ALTER TABLE order_customers ALTER COLUMN work_end_date SET NOT NULL");
        });
    }
}
