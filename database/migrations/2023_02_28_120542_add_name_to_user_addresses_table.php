<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNameToUserAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_addresses', function (Blueprint $table) {
            $table->dropColumn('user_type');
            $table->dropColumn('address_title');
            $table->dropColumn('default_address');
            $table->dropColumn('first_name');
            $table->dropColumn('last_name');
            $table->dropColumn('email');
            $table->dropColumn('mobile');
            $table->dropColumn('address');
            $table->dropColumn('address2');
            $table->dropColumn('other_person_name');
            $table->dropColumn('other_person_mobile');
            $table->string('type')->nullable();
            $table->string('name')->nullable();
            $table->text('desc')->nullable();
            $table->integer('vendor_id')->default(0);
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
        Schema::table('user_addresses', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('name');
            $table->dropColumn('desc');
            $table->dropColumn('vendor_id');
            $table->dropColumn('deleted_at');
            $table->string('user_type')->nullable();
            $table->string('address_title')->nullable();
            $table->string('default_address')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('mobile')->nullable();
            $table->string('address')->nullable();
            $table->string('address2')->nullable();
            $table->string('other_person_name')->nullable();
            $table->string('other_person_mobile')->nullable();
        });
    }
}
