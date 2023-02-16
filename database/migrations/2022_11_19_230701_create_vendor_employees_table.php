<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendorEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendor_employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->string('name')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('password')->nullable();
            $table->string('login_code')->unique()->nullable();
            $table->string('country_code')->nullable();
            $table->string('mobile')->unique()->nullable();
            $table->string('lang')->nullable();
            $table->float('sales_amount')->nullable();
            $table->timestamp('last_seen')->nullable();
            $table->text('permissions')->nullable();
            $table->boolean('hader_status')->default(0)->nullable();
            $table->unsignedBigInteger('period_id')->nullable();
            $table->text('branches')->nullable();
            $table->text('tags')->nullable();
            $table->string('status')->default(0)->nullable();
            $table->boolean('email_receive_messages')->default(0)->nullable();
            $table->boolean('mobile_receive_messages')->default(0)->nullable();

            $table->timestamp('deleted_at')->nullable();

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
        Schema::dropIfExists('vendor_employees');
    }
}
