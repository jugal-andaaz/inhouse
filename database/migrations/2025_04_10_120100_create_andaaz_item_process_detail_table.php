<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('andaaz_item_process_detail', function (Blueprint $table) {
            $table->id(); // id (Auto Increment)
            $table->integer('product_item_id')->nullable();
            $table->string('status_type', 50)->nullable();
            $table->string('status_subtype', 100)->nullable();
            $table->timestamp('created_date')->nullable();
            $table->string('created_by', 25)->nullable();
            $table->tinyInteger('status')->nullable()->default(0);
            $table->string('given_to', 25)->nullable();
            $table->integer('qty')->nullable()->default(0);
            $table->decimal('price', 12, 4)->nullable()->default(0.0000);
            $table->decimal('amount', 12, 4)->nullable()->default(0.0000);
            $table->tinyInteger('pending')->nullable()->default(0);
            $table->string('challan_no', 10)->nullable();
            $table->integer('fabric_length')->nullable();
            $table->string('fabric_type', 100)->nullable();
            $table->integer('new_id')->nullable();
            $table->timestamp('product_complete_date')->nullable();
            $table->timestamp('updated_date')->nullable();
            $table->string('updated_by', 30)->nullable();
            $table->string('qc_status', 30)->nullable();
            $table->string('reason', 50)->nullable();
            $table->string('remark', 250)->nullable();
            $table->string('qc_reject_part', 30)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('andaaz_item_process_detail');
    }
};
