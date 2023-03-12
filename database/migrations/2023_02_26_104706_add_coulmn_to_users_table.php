<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCoulmnToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('vendor_id')->default(0);
            $table->integer('branch_id')->default(0);
            $table->integer('gender')->default(0);
            $table->decimal('deferred_limit',8,2)->default(0);
            $table->string('mobile')->nullable();
            $table->date('birth_date')->nullable();
            $table->boolean('active_deferred')->default(0);
            $table->boolean('is_black_list')->default(0);
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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('vendor_id');
            $table->dropColumn('branch_id');
            $table->dropColumn('gender');
            $table->dropColumn('deferred_limit');
            $table->dropColumn('mobile');
            $table->dropColumn('birth_date');
            $table->dropColumn('active_deferred');
            $table->dropColumn('is_black_list');
        });
    }
}
