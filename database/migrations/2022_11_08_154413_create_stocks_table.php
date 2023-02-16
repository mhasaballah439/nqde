<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_house_id')->nullable();
            $table->string('name')->nullable();
            $table->string('code')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('storage_unit')->nullable();
            $table->string('recipe_unit')->nullable();
            $table->string('recipe_unit_quantity')->nullable();
            $table->string('cost_calculation_method')->nullable();
            $table->float('amount')->nullable();
            $table->string('initial_quantity_to_create_an_order')->nullable();
            $table->string('barcode')->nullable();
            $table->unsignedBigInteger('low_level')->nullable();
            $table->unsignedBigInteger('high_level')->nullable();
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
        Schema::dropIfExists('stocks');
    }
}
