<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveTagsFromBranches extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn('tags');
            $table->dropColumn('delivery_areas');
            $table->dropColumn('users');
            $table->dropColumn('devices');
            $table->dropColumn('discounts');
            $table->dropColumn('fees');
            $table->dropColumn('temporary_events');
            $table->dropColumn('promotions');
            $table->dropColumn('country_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->string('tags')->nullable();
            $table->string('delivery_areas')->nullable();
            $table->string('users')->nullable();
            $table->string('devices')->nullable();
            $table->string('discounts')->nullable();
            $table->string('fees')->nullable();
            $table->string('temporary_events')->nullable();
            $table->string('promotions')->nullable();
            $table->string('country_code')->nullable();
        });
    }
}
