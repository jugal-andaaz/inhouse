<?php

namespace Vanguard\Support\Plugins;

use Vanguard\Plugins\Plugin;
use Vanguard\Support\Sidebar\Item;
use Vanguard\User;

class Order extends Plugin {

    /**
     * 
     * @return Item
     */
    public function sidebar(): Item {
        return Item::create(__('NEW - Inhouse Orders'))
                ->route('orders')
                ->icon('fas fa-server')
                ->active("orders.index")
                ->permissions('orders');
    }
}
