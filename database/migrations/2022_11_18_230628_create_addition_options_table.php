<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdditionOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('addition_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id')->nullable();

            $table->string('name_ar')->nullable();
            $table->string('name_en')->nullable();
            $table->string('code')->nullable();
            $table->string('barcode')->nullable();
            $table->unsignedBigInteger('tax_groups')->nullable();
            $table->string('cost_calculation_method')->default(1)->nullable();
            $table->unsignedBigInteger('type_sell')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->float('price')->nullable();
            $table->string('calories')->nullable();
            $table->text('stocks')->nullable();
            $table->text('custom_price_id')->nullable();
            $table->text('disable_branches')->nullable();
            $table->text('is_empty_branches')->nullable();
            $table->string('status')->default(1)->nullable();
            $table->string('disable_gift_card')->default(1)->nullable();


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
        Schema::dropIfExists('addition_options');
    }
}
