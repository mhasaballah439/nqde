<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveryRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_house_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->float('amount')->nullable();
            $table->string('status')->nullable();
            $table->text('tags')->nullable();
            $table->string('sender')->nullable();
            $table->string('creator')->nullable();
            $table->float('services_amount')->nullable();
            $table->text('notes')->nullable();
            $table->text('stock_units')->nullable();

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
        Schema::dropIfExists('delivery_requests');
    }
}
