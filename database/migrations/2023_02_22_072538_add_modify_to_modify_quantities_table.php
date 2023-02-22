<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddModifyToModifyQuantitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('modify_quantities', function (Blueprint $table) {
            $table->dropColumn('stocks');
            $table->integer('status_id')->default(0);
            $table->string('created_by')->nullable();
            $table->string('send_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('modify_quantities', function (Blueprint $table) {
            $table->dropColumn('status_id');
            $table->dropColumn('created_by');
            $table->dropColumn('send_by');
            $table->text('stocks')->nullable();
        });
    }
}
