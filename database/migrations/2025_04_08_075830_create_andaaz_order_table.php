<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAndaazOrderTable extends Migration
{
    public function up()
    {
        Schema::create('andaaz_order', function (Blueprint $table) {
            $table->id(); // id int(10) Auto Increment
            $table->unsignedInteger('entity_id');
            $table->string('order_status', 255)->nullable();
            $table->string('domain', 255)->nullable();
            $table->unsignedInteger('customer_id')->nullable();
            $table->decimal('subtotal', 12, 4)->nullable();
            $table->decimal('shipping_amount', 12, 4)->nullable();
            $table->string('shipping_description', 255)->nullable();
            $table->integer('discount_amount', false, true)->nullable(); // int(100) isn't valid; using regular int
            $table->decimal('grand_total', 12, 4)->nullable();
            $table->decimal('total_paid', 12, 4)->nullable()->default(0.0000);
            $table->decimal('base_rate', 12, 4)->nullable();
            $table->decimal('order_rate', 12, 4)->nullable();
            $table->string('billing_address_id', 255)->nullable();
            $table->string('shipping_address_id', 255)->nullable();
            $table->string('customer_dob', 255)->nullable();
            $table->string('increment_id', 50)->nullable();
            $table->string('base_currency_code', 255)->nullable();
            $table->string('customer_email', 255)->nullable();
            $table->string('customer_firstname', 255)->nullable();
            $table->string('customer_lastname', 255)->nullable();
            $table->string('customer_middlename', 255)->nullable();
            $table->string('discount_description', 255)->nullable();
            $table->string('ext_order_id', 255)->nullable();
            $table->string('order_currency_code', 255)->nullable();
            $table->string('ipaddress', 255)->nullable();
            $table->string('shipping_method', 255)->nullable();
            $table->string('store_currency_code', 255)->nullable();
            $table->text('customer_note')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->integer('total_item_count')->nullable();
            $table->integer('total_qty_ordered')->nullable();
            $table->string('country_code', 50)->nullable();
            $table->string('discount_code', 255)->nullable();
            $table->string('ordered_currency_code', 255)->nullable();
            $table->string('customer_experience', 10)->nullable();
            $table->integer('flag')->nullable()->default(0);
            $table->date('esd_date')->nullable();
            $table->date('msd_date')->nullable();
            $table->string('order_remark', 255)->nullable();
            $table->string('flag_text', 25)->nullable()->default('Normal');
            $table->dateTime('updated_date')->nullable();
            $table->string('updated_by', 50)->nullable();
            $table->unsignedTinyInteger('customer_group_id')->nullable();
            $table->tinyInteger('custom_status')->nullable()->default(0);
            $table->string('order_from', 35)->nullable();
            $table->string('order_by', 35)->nullable();
            $table->string('order_from_remark', 150)->nullable();
            $table->tinyInteger('rating')->nullable()->default(0);
            $table->tinyInteger('gift_wrap')->nullable()->default(0);
            $table->string('gift_wrap_value', 10)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('andaaz_order');
    }
}
