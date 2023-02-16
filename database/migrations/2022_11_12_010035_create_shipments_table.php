<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShipmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->unsignedFloat('shipping_cost')->nullable();
            $table->integer('from_city_id')->nullable();
            $table->integer('to_city_id')->nullable();
            $table->integer('from_lat')->nullable();
            $table->integer('to_lat')->nullable();
            $table->integer('from_lng')->nullable();
            $table->integer('to_lng')->nullable();
            $table->string('type_user')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('shipping_type')->nullable();
            $table->string('transaction_id')->nullable();
            $table->boolean('status')->default(1);
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
        Schema::dropIfExists('shipments');
    }
}
