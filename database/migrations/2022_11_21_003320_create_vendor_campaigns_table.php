<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendorCampaignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendor_campaigns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->unsignedBigInteger('type')->nullable();
            $table->string('name_ar')->nullable();
            $table->string('name_en')->nullable();
            $table->timestamp('campaigns_start_date')->nullable();
            $table->timestamp('campaigns_end_date')->nullable();
            $table->text('description')->nullable();
            $table->text('campaign_side')->nullable();
            $table->unsignedBigInteger('target_orders')->nullable();
            $table->float('target_amount')->nullable();
            $table->string('domain_name')->nullable();
            $table->string('email_receive')->nullable();
            $table->string('contact_file')->nullable();
            $table->boolean('status')->default(0)->nullable();
            $table->boolean('approve')->default(0)->nullable();
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
        Schema::dropIfExists('vendor_campaigns');
    }
}
