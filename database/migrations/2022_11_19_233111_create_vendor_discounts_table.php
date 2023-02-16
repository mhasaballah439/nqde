<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendorDiscountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendor_discounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->string('name_ar')->nullable();
            $table->string('name_en')->nullable();
            $table->string('use_for')->nullable();
            $table->string('discount_type')->default(0)->nullable();
            $table->string('discount_amount_by_order')->nullable();
            $table->string('discount_percentage_by_order')->nullable();
            $table->string('discount_amount_by_product')->nullable();
            $table->string('discount_percentage_by_product')->nullable();
            $table->string('discount_amount_by_order_product')->nullable();
            $table->string('discount_percentage_by_order_product')->nullable();
            $table->text('orders_type')->nullable();
            $table->string('operation_number')->unique()->nullable();
            $table->boolean('tax_discount_amount')->default(0)->nullable();
            $table->text('categories')->nullable();
            $table->text('products')->nullable();
            $table->text('product_collections')->nullable();
            $table->text('product_tags')->nullable();
            $table->text('client_tags')->nullable();
            $table->string('status')->nullable();

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
        Schema::dropIfExists('vendor_discounts');
    }
}
