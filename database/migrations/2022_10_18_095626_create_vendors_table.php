<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('family_name')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('status')->nullable();
            $table->bigInteger('account_number')->unique()->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->string('activity_name')->nullable();
            $table->unsignedBigInteger('currency')->nullable();
            $table->string('country_code')->nullable();
            $table->string('mobile')->nullable();
            $table->unsignedBigInteger('activity_id')->nullable();
            $table->boolean('is_multi_store')->default(0)->nullable();
            $table->string('commercial_registration_number')->nullable();
            $table->string('municipal_license_number')->nullable();
            $table->string('tax_number')->nullable();
            $table->boolean('has_block')->default(0)->nullable();
            $table->boolean('has_device_block')->default(0)->nullable();
            $table->string('block_title')->nullable();
            $table->text('block_reason')->nullable();
            $table->timestamp('deleted_at')->nullable();
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
        Schema::dropIfExists('vendors');
    }
}
