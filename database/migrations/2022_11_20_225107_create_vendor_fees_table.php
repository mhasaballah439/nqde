<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendorFeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendor_fees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->string('name_ar')->nullable();
            $table->string('name_en')->nullable();
            $table->string('type')->default(1)->nullable();
            $table->boolean('open_amount')->default(0)->nullable();
            $table->float('amount')->nullable();
            $table->float('percentage_amount')->nullable();
            $table->text('order_type')->nullable();
            $table->text('branches')->nullable();
            $table->text('taxes_group')->nullable();
            $table->boolean('auto_apply')->default(0)->nullable();
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
        Schema::dropIfExists('vendor_fees');
    }
}
