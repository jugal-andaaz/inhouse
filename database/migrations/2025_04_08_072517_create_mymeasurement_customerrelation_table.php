<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMymeasurementCustomerrelationTable extends Migration
{
    public function up()
    {
        Schema::create('mymeasurement_customerrelation', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('quote_id')->nullable();
            $table->string('order_no', 255)->nullable();
            $table->string('customer_id', 255)->nullable();
            $table->string('store_id', 255)->nullable();
            $table->unsignedInteger('quote_item_id')->nullable();
            $table->unsignedInteger('order_item_id')->nullable();
            $table->string('store', 255)->nullable();
            $table->string('mm_profile_id', 255)->nullable();

            // Foreign key relation
            /*$table->unsignedBigInteger('mm_profile_id')->nullable();
            $table->foreign('mm_profile_id')->references('id')->on('mymeasurement_profile')->onDelete('cascade');*/

            $table->date('created_date')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mymeasurement_customerrelation');
    }
}
