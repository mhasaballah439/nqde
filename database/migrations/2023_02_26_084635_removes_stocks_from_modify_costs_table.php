<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemovesStocksFromModifyCostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('modify_costs', function (Blueprint $table) {
            $table->dropColumn('stocks');
            $table->integer('status_id')->default(0);
            $table->integer('vendor_id')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('modify_costs', function (Blueprint $table) {
            $table->dropColumn('status_id');
            $table->dropColumn('vendor_id');
            $table->text('stocks')->nullable();
        });
    }
}
