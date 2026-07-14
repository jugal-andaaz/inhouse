<?php

namespace Vanguard\Support\Plugins;

use Vanguard\Plugins\Plugin;
use Vanguard\Support\Sidebar\Item;

class RunningOrders extends Plugin
{
    public function sidebar(): Item
    {
        return Item::create(__('RUNNING ORDERS'))
            ->route('reports.newitemlogstracker')
            ->icon('fas fa-server')
            ->active('newitemlogstracker*')
            ->permissions('orderreportstatus');
    }
}
