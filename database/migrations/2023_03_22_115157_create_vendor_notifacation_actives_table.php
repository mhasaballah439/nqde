<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendorNotifacationActivesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendor_notifacation_actives', function (Blueprint $table) {
            $table->id();
            $table->integer('vendor_id')->default(0);
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('active_web_notify')->nullable();
            $table->boolean('active_cashier_notify')->nullable();
            $table->boolean('active_out_of_stock_notify')->nullable();
            $table->boolean('active_branches_working_hours_notify')->nullable();
            $table->boolean('active_requests_notify')->nullable();
            $table->boolean('active_employees_notify')->nullable();
            $table->boolean('active_devices_notify')->nullable();
            $table->boolean('active_term_account_notify')->nullable();
            $table->boolean('active_gift_cards_notify')->nullable();
            $table->boolean('active_discount_codes_notify')->nullable();
            $table->boolean('active_temporary_events_notify')->nullable();
            $table->boolean('active_preparation_delay_notify')->nullable();
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
        Schema::dropIfExists('vendor_notifacation_actives');
    }
}
