<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockPurchaseOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->integer('vendor_id')->default(0);
            $table->integer('supplier_id')->default(0);
            $table->integer('branch_id')->default(0);
            $table->integer('status_id')->default(0);
            $table->dateTime('work_date')->nullable();
            $table->dateTime('delivery_date')->nullable();
            $table->string('sender_name')->nullable();
            $table->string('approve_by_name')->nullable();
            $table->string('code')->nullable();
            $table->text('notes')->nullable();
            $table->string('created_by_name')->nullable();
            $table->decimal('extra_price',8,2)->default(0);
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
        Schema::dropIfExists('stock_purchase_orders');
    }
}
