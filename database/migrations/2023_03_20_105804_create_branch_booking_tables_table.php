<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBranchBookingTablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('branch_booking_tables', function (Blueprint $table) {
            $table->id();
            $table->integer('vendor_id')->default(0);
            $table->integer('branch_id')->default(0);
            $table->decimal('period_hours',8,2)->default(0);
            $table->text('tables')->nullable();
            $table->boolean('active')->default(0);
            $table->time('saturday')->nullable();
            $table->time('sunday')->nullable();
            $table->time('monday')->nullable();
            $table->time('tuesday')->nullable();
            $table->time('wednesday')->nullable();
            $table->time('thursday')->nullable();
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
        Schema::dropIfExists('branch_booking_tables');
    }
}
