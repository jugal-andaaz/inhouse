<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateShipmentTrackingUniqueConstraint extends Migration
{
    public function up()
    {
        Schema::table('shipment_tracking', function (Blueprint $table) {
            $table->dropUnique('shipment_tracking_carrier_tracking_number_unique');
            $table->unique(['carrier', 'tracking_number', 'unique_id'], 'unique_tracking');
        });
    }

    public function down()
    {
        Schema::table('shipment_tracking', function (Blueprint $table) {
            $table->dropUnique('unique_tracking');
            $table->unique(['carrier', 'tracking_number'], 'shipment_tracking_carrier_tracking_number_unique');
        });
    }
}
