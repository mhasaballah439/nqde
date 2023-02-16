<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendorCustomerScreenDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendor_customer_screen_devices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('device_id')->nullable();
            $table->unsignedBigInteger('cashier_auto_connect_id')->nullable();
            $table->unsignedBigInteger('silent_mentis')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->boolean('disable_manual_control_device')->default(0)->nullable();
            $table->boolean('disable_view_order_to_customer')->default(0)->nullable();
            $table->boolean('disable_view_ads_to_customer')->default(0)->nullable();
            $table->boolean('enable_scan_nfc')->default(0)->nullable();


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
        Schema::dropIfExists('vendor_customer_screen_devices');
    }
}
