<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendorScreenPreparationDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendor_screen_preparation_devices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('device_id')->nullable();
            $table->text('receive_orders_type')->nullable();
            $table->text('devices_connect')->nullable();
            $table->text('tags')->nullable();
            $table->unsignedBigInteger('limit_order_number')->nullable();
            $table->string('email_receive_end_day')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('silent_mentis')->nullable();
            $table->boolean('notify_pass_preparation_time')->default(0)->nullable();
            $table->boolean('connect_other_devices')->default(0)->nullable();
            $table->boolean('close_order_pass_preparation_time')->default(0)->nullable();
            $table->boolean('receive_online_orders')->default(0)->nullable();
            $table->boolean('disable_receive_cashier_notes')->default(0)->nullable();



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
        Schema::dropIfExists('vendor_screen_preparation_devices');
    }
}
