<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendorGiftCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendor_gift_cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id')->nullable();

            $table->string('name_ar')->nullable();
            $table->string('name_en')->nullable();
            $table->string('code')->nullable();
            $table->string('barcode')->nullable();
            $table->string('cost_calculation_method')->default(1)->nullable();
            $table->float('price')->nullable();
            $table->string('gift_card_number')->nullable();
            $table->string('status')->nullable();
            $table->text('categories')->nullable();
            $table->text('tags')->nullable();
            $table->text('is_empty_branches')->nullable();

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
        Schema::dropIfExists('vendor_gift_cards');
    }
}
