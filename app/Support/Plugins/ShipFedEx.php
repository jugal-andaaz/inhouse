<?php

namespace Vanguard\Support\Plugins;

use Vanguard\Plugins\Plugin;
use Vanguard\Support\Sidebar\Item;

class ShipFedEx extends Plugin {

    public function sidebar(): Item {
        return Item::create('Ship FedEx')
                ->href('/fedex/shipment/create')
                ->icon('fas fa-plane')
                ->active('fedex/shipment/create');
    }
}
