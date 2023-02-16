<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBranchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->string('name_ar')->nullable();
            $table->string('name_en')->nullable();
            $table->string('code')->unique()->nullable();
            $table->unsignedBigInteger('tax_groups')->nullable();
            $table->unsignedBigInteger('tax_number')->nullable();
            $table->string('tax_registration_name')->nullable();
            $table->string('country_code')->nullable();
            $table->string('mobile')->nullable();
            $table->string('lat')->nullable();
            $table->string('long')->nullable();
            $table->text('up_invoices')->nullable();
            $table->text('down_invoices')->nullable();
            $table->string('address')->nullable();
            $table->string('status')->nullable();
            $table->boolean('receive_order_from_api')->default(0)->nullable();
            $table->text('tags')->nullable();
            $table->text('delivery_areas')->nullable();
            $table->text('users')->nullable();
            $table->text('tables')->nullable();
            $table->text('devices')->nullable();
            $table->text('discounts')->nullable();
            $table->text('fees')->nullable();
            $table->text('temporary_events')->nullable();
            $table->text('promotions')->nullable();




            $table->timestamp('start_work_time')->nullable();
            $table->timestamp('end_work_time')->nullable();
            $table->timestamp('end_stock_date')->nullable();
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
        Schema::dropIfExists('branches');
    }
}
