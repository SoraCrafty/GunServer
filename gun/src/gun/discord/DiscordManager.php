<?php

namespace gun\discord;

use gun\provider\ProviderManager;
use gun\provider\DiscordProvider;

class DiscordManager{

	private $plugin;
	private $provider;

    public function __construct($plugin)
    {
    	$this->plugin = $plugin;
    	$this->provider = ProviderManager::get(DiscordProvider::PROVIDER_ID);
    }

    public function sendMessage($message)
    {
    	if(!$this->provider->isEnable()) return false;
    	
		$message = [
				  'username' => $this->provider->getUserName(),
				  'content' => $message,
				];
    	$this->plugin->getServer()->getAsyncPool()->submitTask(new AsyncSendTask($message, $this->provider->getWebhook()));
    	return true;
    }

}
