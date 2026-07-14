<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAndaazOrderAddressTable extends Migration
{
    public function up()
    {
        Schema::create('andaaz_order_address', function (Blueprint $table) {
            $table->increments('entity_id'); // Auto-incremented primary key
            $table->unsignedInteger('parent_id')->nullable();
            $table->integer('customer_address_id')->nullable();
            $table->integer('quote_address_id')->nullable();
            $table->integer('region_id')->nullable();
            $table->integer('customer_id')->nullable();
            $table->string('fax', 255)->nullable();
            $table->string('region', 255)->nullable();
            $table->string('postcode', 255)->nullable();
            $table->string('lastname', 255)->nullable();
            $table->string('street', 255)->nullable();
            $table->string('city', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('telephone', 255)->nullable();
            $table->string('country_id', 2)->nullable(); // ISO 2-character country code
            $table->string('firstname', 255)->nullable();
            $table->string('address_type', 255)->nullable();
            $table->dateTime('created_date')->nullable();
            $table->string('created_by', 35)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('andaaz_order_address');
    }
}
