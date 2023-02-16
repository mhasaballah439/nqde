<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DeleteTagsFromSuppliersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('country_code');
            $table->dropColumn('tags');
            $table->dropColumn('supplier_products');
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
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
            $table->string('country_code')->nullable();
            $table->string('tags')->nullable();
            $table->string('supplier_products')->nullable();
        });
    }
}
