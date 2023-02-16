<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVendorIdToStockProductionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stock_productions', function (Blueprint $table) {
            $table->integer('vendor_id')->default(0);
            $table->integer('status_id')->default(0);
            $table->string('code')->nullable();
            $table->dropColumn('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stock_productions', function (Blueprint $table) {
            $table->dropColumn('vendor_id');
            $table->dropColumn('status_id');
            $table->dropColumn('code');
            $table->string('status')->nullable();
        });
    }
}
