<?php

namespace gun\events;

use gun\provider\AccountProvider;
use gun\provider\ProviderManager;

class PlayerLoginEvent extends Events
{

	public function call($event)
    {
        $player = $event->getPlayer();
        $provider = ProviderManager::get(AccountProvider::PROVIDER_ID);
        if(!$provider->isRegistered($player)) $provider->register($player);
	}

}
