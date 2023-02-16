<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendorCasherDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendor_casher_devices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('device_id')->nullable();
            $table->unsignedBigInteger('start_order_number')->nullable();
            $table->unsignedBigInteger('end_order_number')->nullable();
            $table->text('default_orders_type')->nullable();
            $table->text('default_orders_disable')->nullable();
            $table->text('kitchen_print_language')->nullable();
            $table->text('order_tags')->nullable();
            $table->string('email_receive_end_day')->nullable();
            $table->string('email_custody_end_day')->nullable();
            $table->string('email_shifts_end_day')->nullable();
            $table->text('tables')->nullable();
            $table->unsignedBigInteger('silent_mentis')->nullable();
            $table->string('invoice_size')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->text('employees_users')->nullable();
            $table->unsignedBigInteger('notify_next_order_mentis')->nullable();
            $table->boolean('connect_other_devices')->default(0)->nullable();
            $table->boolean('barcode_reader')->default(0)->nullable();
            $table->boolean('online_order_accept')->default(0)->nullable();
            $table->boolean('print_online_order')->default(0)->nullable();
            $table->boolean('send_next_order_to_preparation_device')->default(0)->nullable();
            $table->boolean('auto_print_invoice')->default(0)->nullable();
            $table->boolean('use_notify_number_from_main_cashier')->default(0)->nullable();
            $table->boolean('print_box_operation')->default(0)->nullable();
            $table->boolean('Force_price_operations')->default(0)->nullable();
            $table->boolean('print_sales_end_day')->default(0)->nullable();
            $table->boolean('enable_rewards_scanner')->default(0)->nullable();
            $table->boolean('force_seat')->default(0)->nullable();
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
        Schema::dropIfExists('vendor_casher_devices');
    }
}
