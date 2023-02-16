<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNqdeStoreCartCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nqde_store_cart_coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('amount_type')->nullable();
            $table->unsignedBigInteger('amount')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamp('expire')->nullable();
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
        Schema::dropIfExists('nqde_store_cart_coupons');
    }
}
