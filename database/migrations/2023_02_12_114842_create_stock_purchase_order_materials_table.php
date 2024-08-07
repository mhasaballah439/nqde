<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockPurchaseOrderMaterialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_purchase_order_materials', function (Blueprint $table) {
            $table->id();
            $table->integer('stock_purchase_order_id')->default(0);
            $table->integer('stock_material_id')->default(0);
            $table->integer('type')->default(0);
            $table->decimal('qty',8,2)->default(0);
            $table->decimal('price',8,2)->default(0);
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
        Schema::dropIfExists('stock_purchase_order_materials');
    }
}
