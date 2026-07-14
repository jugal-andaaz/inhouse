<?php

use Illuminate\Database\Migrations\Migration;
use Vanguard\Permission;

class AddFedexShipmentPermission extends Migration
{
    public function up()
    {
        if (!Permission::where('name', 'fedexftb')->exists()) {
            Permission::create([
                'name'         => 'fedexftb',
                'display_name' => 'FedEx FTB - Create Shipment',
                'description'  => 'Access to create FedEx shipments and download labels.',
                'removable'    => true,
            ]);
        }
    }

    public function down()
    {
        Permission::where('name', 'fedexftb')->delete();
    }
}
