<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyDataToVendorEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vendor_employees', function (Blueprint $table) {
            $table->dropColumn('country_code');
            $table->dropColumn('last_seen');
            $table->dropColumn('permissions');
            $table->dropColumn('branch_id');
            $table->dropColumn('branches');
            $table->dropColumn('tags');
            $table->dropColumn('hader_status');
            $table->integer('vendor_id')->default(0);
            $table->integer('role_id')->default(0);
            $table->integer('rosacea')->default(0);
            $table->string('currency')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vendor_employees', function (Blueprint $table) {
            $table->string('country_code')->nullable();
            $table->string('last_seen')->nullable();
            $table->string('permissions')->nullable();
            $table->string('branch_id')->nullable();
            $table->string('branches')->nullable();
            $table->string('tags')->nullable();
            $table->string('hader_status')->nullable();
            $table->dropColumn('vendor_id');
            $table->dropColumn('role_id');
            $table->dropColumn('rosacea');
            $table->dropColumn('currency');
        });
    }
}
