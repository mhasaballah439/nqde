<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdditionOptionsSpecialBranchPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('addition_options_special_branch_prices', function (Blueprint $table) {
            $table->id();
            $table->integer('additional_option_id')->default(0);
            $table->integer('branch_id')->default(0);
            $table->decimal('price',8,2)->default(0);
            $table->boolean('active')->default(0);
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
        Schema::dropIfExists('addition_options_special_branch_prices');
    }
}
