<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentToStoreHousesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('store_houses', function (Blueprint $table) {
            $table->text('payment')->nullable();
            $table->boolean('is_payment')->default(0);
            $table->boolean('is_free')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('store_houses', function (Blueprint $table) {
            $table->dropColumn('payment');
            $table->dropColumn('is_payment');
            $table->dropColumn('is_free');
        });
    }
}
