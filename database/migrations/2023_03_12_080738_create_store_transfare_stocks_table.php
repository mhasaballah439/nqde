<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoreTransfareStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_transfare_stocks', function (Blueprint $table) {
            $table->id();
            $table->integer('stock_id')->default(0);
            $table->integer('store_transfare_id')->default(0);
            $table->integer('type')->default(0);
            $table->decimal('qty',8,2)->default(0);
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
        Schema::dropIfExists('store_transfare_stocks');
    }
}
