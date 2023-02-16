<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStartDateToVendorPlanes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vendor_planes', function (Blueprint $table) {
            $table->text('payment')->nullable();
            $table->boolean('is_payment')->default(0);
            $table->date('st_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('price',8,2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vendor_planes', function (Blueprint $table) {
            $table->dropColumn('payment');
            $table->dropColumn('is_payment');
            $table->dropColumn('st_date');
            $table->dropColumn('end_date');
            $table->dropColumn('price');
        });
    }
}
