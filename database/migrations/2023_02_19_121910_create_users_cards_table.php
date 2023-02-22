<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_cards', function (Blueprint $table) {
            $table->id();
            $table->integer('vendor_id')->default(0);
            $table->string('card_number')->nullable();
            $table->integer('ex_month')->nullable();
            $table->integer('ex_year')->nullable();
            $table->integer('cvv')->nullable();
            $table->string('holder_name')->nullable();
            $table->string('payment_id')->nullable();
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
        Schema::dropIfExists('users_cards');
    }
}
