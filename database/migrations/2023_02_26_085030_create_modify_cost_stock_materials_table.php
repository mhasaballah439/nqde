<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModifyCostStockMaterialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('modify_cost_stock_materials', function (Blueprint $table) {
            $table->id();
            $table->integer('modify_cost_id')->default(0);
            $table->integer('stock_material_id')->default(0);
            $table->integer('type')->default(0);
            $table->decimal('cost',8,2)->default(0);
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
        Schema::dropIfExists('modify_cost_stock_materials');
    }
}
