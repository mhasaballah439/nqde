<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendorDiscountCoustomerTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendor_discount_coustomer_tags', function (Blueprint $table) {
            $table->id();
            $table->integer('tag_id')->default(0);
            $table->integer('type_id')->default(0);
            $table->integer('discount_id')->default(0);
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
        Schema::dropIfExists('vendor_discount_coustomer_tags');
    }
}
