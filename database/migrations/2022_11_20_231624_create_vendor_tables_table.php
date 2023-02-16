<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendorTablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendor_tables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('table_category_id')->nullable();
            $table->unsignedBigInteger('table_number')->nullable();
            $table->unsignedBigInteger('chairs_number')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vendor_tables');
    }
}
