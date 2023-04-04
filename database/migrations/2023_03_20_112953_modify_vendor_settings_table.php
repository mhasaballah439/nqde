<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyVendorSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vendor_settings', function (Blueprint $table) {
            $table->dropColumn('key');
            $table->integer('vendor_id')->default(0);
            $table->integer('country_id')->default(0);
            $table->integer('currency_id')->default(0);
            $table->integer('time_zone_id')->default(0);
            $table->string('work_name')->nullable();
            $table->string('name_tax_registry')->nullable();
            $table->string('tax_registration_number')->nullable();
            $table->boolean('prices_include_tax')->default(0);
            $table->boolean('restrict_stocks_single_supplier')->default(0);
            $table->boolean('active_en_lang')->default(0);
            $table->integer('loyalty_app_method')->default(0);
            $table->integer('loyalty_app_reward_type')->default(0);
            $table->decimal('loyalty_app_min_order_price',8,2)->default(0);
            $table->decimal('loyalty_app_reward_discount',8,2)->default(0);
            $table->decimal('loyalty_app_max_discount_amount',8,2)->default(0);
            $table->integer('loyalty_app_delay_earning_points_minute')->default(0);
            $table->integer('loyalty_app_number_bonus_orders_required')->default(0);
            $table->integer('loyalty_app_reward_points_required')->default(0);
            $table->boolean('loyalty_app_send_sms_notify')->default(0);
            $table->boolean('loyalty_app_send_email_notify')->default(0);
            $table->integer('loyalty_app_bonus_validity_days')->default(0);
            $table->decimal('loyalty_app_bonus_price',8,2)->default(0);
            $table->integer('invoice_print_lang')->default(0);
            $table->integer('invoice_main_lang')->default(0);
            $table->integer('invoice_second_lang')->default(0);
            $table->string('invoice_header')->nullable();
            $table->string('invoice_footer')->nullable();
            $table->integer('invoice_size_id')->default(0);
            $table->string('invoice_address')->nullable();
            $table->boolean('invoice_insert_social_media_account')->default(0);
            $table->string('invoice_website')->nullable();
            $table->string('invoice_facebook')->nullable();
            $table->string('invoice_instagram')->nullable();
            $table->string('invoice_snap_chat')->nullable();
            $table->string('invoice_twitter')->nullable();
            $table->string('invoice_youtube')->nullable();
            $table->boolean('invoice_view_order_number')->default(0);
            $table->boolean('invoice_calorie_display')->default(0);
            $table->boolean('invoice_view_subtotal')->default(0);
            $table->boolean('invoice_display_user_name')->default(0);
            $table->boolean('invoice_show_check_number')->default(0);
            $table->boolean('invoice_hide_free_additions')->default(0);
            $table->boolean('invoice_show_customer_data')->default(0);
            $table->boolean('invoice_activate_billing_qrcode')->default(0);
            $table->boolean('invoice_activate_electronic_invoice')->default(0);
            $table->integer('call_center_payment_method')->default(0);
            $table->integer('call_center_employee')->default(0);
            $table->text('call_center_deactive_branches')->nullable();
            $table->text('call_center_deactive_order_types')->nullable();
            $table->text('call_center_list_set')->nullable();
            $table->boolean('call_center_active_discounts')->default(0);
            $table->decimal('cashier_predetermined_payment_amounts',8,2)->default(0);
            $table->decimal('cashier_payment_coins',8,2)->default(0);
            $table->decimal('cashier_predetermined_tip_percentage',8,2)->default(0);
            $table->integer('cashier_delays_raising_requests_minute')->default(0);
            $table->integer('cashier_logout_inactive_users_minute')->default(0);
            $table->integer('cashier_maximum_return_period_orders_minute')->default(0);
            $table->string('cashier_request_order_signs_orders')->nullable();
            $table->string('cashier_punctuation_method')->nullable();
            $table->string('cashier_sorting_method_kitchen')->nullable();
            $table->boolean('cashier_activate_perks')->default(0);
            $table->boolean('cashier_discount_requires_customer_information')->default(0);
            $table->boolean('cashier_cancellation_requires_customer_information')->default(0);
            $table->boolean('cashier_table_selection_number_visitors_mandatory')->default(0);
            $table->boolean('cashier_always_reason_cancellation')->default(0);
            $table->boolean('cashier_send_order_kitchen_automatically_after_payment')->default(0);
            $table->boolean('cashier_data_synchronization_tart_workday')->default(0);
            $table->boolean('cashier_print_rounded_products_automatically')->default(0);
            $table->boolean('cashier_prevent_ending_day_before_inventory_count')->default(0);
            $table->boolean('cashier_print_end_day_report_automatically_after_closing_day')->default(0);
            $table->string('stock_header')->nullable();
            $table->string('stock_footer')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vendor_settings');
    }
}
