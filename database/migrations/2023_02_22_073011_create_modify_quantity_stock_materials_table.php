<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModifyQuantityStockMaterialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('modify_quantity_stock_materials', function (Blueprint $table) {
            $table->id();
            $table->integer('modify_quantity_id')->default(0);
            $table->integer('stock_material_id')->default(0);
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
        Schema::dropIfExists('modify_quantity_stock_materials');
    }
}
