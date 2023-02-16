<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendorPromotionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendor_promotions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->string('name_ar')->nullable();
            $table->string('name_en')->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->text('days')->nullable();
            $table->text('order_type')->nullable();
            $table->string('priority')->nullable();
            $table->boolean('inclusion_adds')->nullable();
            $table->boolean('inclusion_vendor_store')->nullable();
            $table->unsignedBigInteger('promotion_type')->nullable();
            $table->unsignedBigInteger('discount_type')->nullable();
            $table->float('discount_amount')->nullable();
            $table->float('limit_discount_amount')->nullable();
            $table->unsignedBigInteger('client_type_offer')->nullable();
            $table->unsignedBigInteger('quantity')->nullable();
            $table->unsignedBigInteger('amount')->nullable();
            $table->text('products')->nullable();
            $table->text('tags')->nullable();
            $table->text('categories')->nullable();
            $table->boolean('has_discount_on_order')->default(0)->nullable();
            $table->boolean('has_discount_on_product')->default(0)->nullable();
            $table->boolean('pay_fixed_amount')->default(0)->nullable();
            $table->unsignedBigInteger('product_number')->nullable();
            $table->text('branches')->nullable();
            $table->text('customer_tags')->nullable();
            $table->string('status')->nullable(1)->nullable();

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
        Schema::dropIfExists('vendor_promotions');
    }
}
