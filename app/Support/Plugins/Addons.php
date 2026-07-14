<?php

namespace Vanguard\Support\Plugins;

use Vanguard\Plugins\Plugin;
use Vanguard\Support\Sidebar\Item;

class Addons extends Plugin
{
    public function sidebar(): Item
    {
        return Item::create(__('Addons'))
            ->route('addons.index')
            ->icon('fas fa-users')
            ->active('addons*')
            ->permissions('addons');
    }
}
