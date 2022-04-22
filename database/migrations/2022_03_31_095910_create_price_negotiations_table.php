<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePriceNegotiationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('price_negotiations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->enum('type', ['contract_work', 'contract_home_method']);
            $table->string('number')->nullable();
            $table->timestamp('date')->nullable();
            $table->string('organization_status');
            $table->unsignedBigInteger('order_id');
            $table->string('responsible_full_name')->nullable();
            $table->string('responsible_phone')->nullable();
            $table->text('comment')->nullable();
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
        Schema::dropIfExists('price_negotiations');
    }
}
