<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockPurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_purchases', function (Blueprint $table) {
            $table->id();
            $table->integer('stock_purchase_order_id')->default(0);
            $table->integer('vendor_id')->default(0);
            $table->integer('status_id')->default(0);
            $table->integer('supplier_id')->default(0);
            $table->integer('branch_id')->default(0);
            $table->string('code')->default(0);
            $table->string('created_by_name')->default(0);
            $table->string('sender_by_name')->default(0);
            $table->string('invoice_number')->default(0);
            $table->text('notes')->nullable();
            $table->dateTime('receipt_date')->nullable();
            $table->dateTime('invoice_date')->nullable();
            $table->decimal('taxes_price',8,2)->default(0);
            $table->decimal('sub_total',8,2)->default(0);
            $table->decimal('extra_price',8,2)->default(0);
            $table->decimal('total',8,2)->default(0);
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
        Schema::dropIfExists('stock_purchases');
    }
}
