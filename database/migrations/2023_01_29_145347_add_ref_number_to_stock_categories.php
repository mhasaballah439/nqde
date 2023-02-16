<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRefNumberToStockCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stock_categories', function (Blueprint $table) {
            $table->integer('vendor_id')->default(0);
            $table->string('add_by')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stock_categories', function (Blueprint $table) {
            $table->dropColumn('vendor_id');
            $table->dropColumn('deleted_at');
            $table->dropColumn('add_by');
        });
    }
}
