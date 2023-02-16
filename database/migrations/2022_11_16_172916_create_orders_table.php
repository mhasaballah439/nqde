<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('number')->nullable();
            $table->unsignedBigInteger('order_number')->nullable();
            $table->unsignedBigInteger('order_type_id')->nullable();
            $table->string('status')->nullable();
            $table->string('source')->nullable();
            $table->timestamp('open_time')->nullable();
            $table->timestamp('close_time')->nullable();
            $table->timestamp('pickup_time')->nullable();
            $table->unsignedBigInteger('visitors')->nullable();
            $table->unsignedBigInteger('table_id')->nullable();
            $table->string('creator')->nullable();
            $table->string('closer')->nullable();
            $table->float('sub_amount')->nullable();
            $table->float('discount')->nullable();
            $table->float('discount_type')->nullable();
            $table->float('tax_total')->nullable();
            $table->float('total_fees')->nullable();
            $table->float('amount')->nullable();
            $table->text('tags')->nullable();



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
        Schema::dropIfExists('orders');
    }
}
