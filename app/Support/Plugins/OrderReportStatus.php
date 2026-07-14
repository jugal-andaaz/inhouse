<?php

namespace Vanguard\Support\Plugins;

use Vanguard\Plugins\Plugin;
use Vanguard\Support\Sidebar\Item;

class OrderReportStatus extends Plugin
{
    public function sidebar(): Item
    {
        return Item::create(__('Order Report Status'))
            ->route('reports.orderstatus')
            ->icon('fas fa-users')
            ->active('orderreportstatus*')
            ->permissions('orderreportstatus');
    }
}
