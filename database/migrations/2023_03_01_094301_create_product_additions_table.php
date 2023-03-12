<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductAdditionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_additions', function (Blueprint $table) {
            $table->id();
            $table->integer('product_id')->default(0);
            $table->integer('addition_id')->default(0);
            $table->integer('max_choice')->default(0);
            $table->integer('min_choice')->default(0);
            $table->integer('free_choice')->default(0);
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
        Schema::dropIfExists('product_additions');
    }
}
