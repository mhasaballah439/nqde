<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoreTransfaresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_transfares', function (Blueprint $table) {
            $table->id();
            $table->integer('vendor_id')->default(0);
            $table->integer('from_branch_id')->default(0);
            $table->integer('to_branch_id')->default(0);
            $table->integer('from_store_house_id')->default(0);
            $table->integer('to_store_house_id')->default(0);
            $table->integer('status_id')->default(0);
            $table->integer('transfare_type')->default(0);
            $table->string('code')->nullable();
            $table->string('sender')->nullable();
            $table->string('created_by_name')->nullable();
            $table->string('extra_price_name')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('extra_price',8,2)->default(0);
            $table->dateTime('work_date')->nullable();
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
        Schema::dropIfExists('store_transfares');
    }
}
