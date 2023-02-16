<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCoulmnToBouquets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bouquets', function (Blueprint $table) {
            $table->dropColumn('expire');
            $table->dropColumn('trail_period_number');
            $table->decimal('branch_price',8,2)->default(0);
            $table->decimal('warehouse_price',8,2)->default(0);
            $table->integer('report_id')->default(0);
            $table->integer('trail_days')->default(0);
            $table->integer('apps_id')->default(0);
            $table->text('allowed_apps')->nullable();
            $table->text('allowed_reports')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bouquets', function (Blueprint $table) {
            $table->dropColumn('branch_price');
            $table->dropColumn('warehouse_price');
            $table->dropColumn('report_id');
            $table->dropColumn('apps_id');
            $table->dropColumn('trail_days');
            $table->dropColumn('allowed_apps');
            $table->dropColumn('allowed_reports');
        });
    }
}
