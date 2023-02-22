<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentBrandTUsersCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_cards', function (Blueprint $table) {
            $table->text('brand')->nullable();
            $table->integer('last_4number')->nullable();
            $table->boolean('is_default')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
         Schema::table('users_cards', function (Blueprint $table) {
            $table->dropColumn('brand');
            $table->dropColumn('last_4number');
            $table->dropColumn('is_default');
        });
    }
}
