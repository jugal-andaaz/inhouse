<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('andaaz_inhouse_country_name', function (Blueprint $table) {
            $table->increments('id'); // int(10) auto-increment
            $table->char('country_code', 5);
            $table->char('country_name', 30)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('andaaz_inhouse_country_name');
    }
};