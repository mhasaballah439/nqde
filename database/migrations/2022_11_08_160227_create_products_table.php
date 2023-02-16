<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('code')->nullable();
            $table->string('name_ar')->nullable();
            $table->string('name_en')->nullable();
            $table->string('pricing_method')->nullable();
            $table->float('price')->nullable();
            $table->string('retail_product')->default(0)->nullable();
            $table->unsignedBigInteger('type_sell')->nullable();
            $table->unsignedBigInteger('tax_groups')->nullable();
            $table->string('barcode')->nullable();
            $table->string('cost_calculation_method')->default(1)->nullable();
            $table->timestamp('preparation_time')->nullable();
            $table->string('calories')->nullable();
            $table->bigInteger('number_people')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->text('tags')->nullable();
            $table->text('additions')->nullable();
            $table->text('stocks')->nullable();
            $table->text('custom_price_id')->nullable();
            $table->text('disable_branches')->nullable();
            $table->text('is_empty_branches')->nullable();
            $table->text('product_collections')->nullable();
            $table->text('temporary_events')->nullable();
            $table->text('attributes')->nullable();
            $table->string('status')->default(1)->nullable();
            $table->timestamp('expire')->nullable();
            $table->timestamp('deleted_at')->nullable();
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
        Schema::dropIfExists('products');
    }
}
