<?php

namespace Vanguard\Support\Plugins;

use Vanguard\Plugins\Plugin;
use Vanguard\Support\Sidebar\Item;
use Vanguard\User;

class OldOrder extends Plugin {
    /**
     * 
     * @return Item
     */
    public function sidebar(): Item {
        return Item::create(__('OLD - Inhouse Orders'))
                ->route('oldorders')
                ->icon('fas fa-server')
                ->active("oldorders.index")
                ->permissions('oldorders');
    }
}
