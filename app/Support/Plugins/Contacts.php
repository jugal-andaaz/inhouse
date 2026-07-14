<?php
 
namespace Vanguard\Support\Plugins;

use Vanguard\Plugins\Plugin;
use Vanguard\Support\Sidebar\Item; 
use Vanguard\User;

class Contacts extends Plugin
{
    public function sidebar(): Item
    {
        return Item::create(__('Contacts'))
            ->route('contact')
            ->active('contact.index')
            ->permissions(function (User $user) {
                return $user->hasPermission('contacts');
            }); 
    }
}
