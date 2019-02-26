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
    	if(!$this->provider->isEnable() || $this->provider->getWebhook() === "") return false;
    	
		$message = [
				  'username' => $this->provider->getUserName(),
				  'content' => $message,
				];
    	$this->plugin->getServer()->getAsyncPool()->submitTask(new AsyncSendTask($message, $this->provider->getWebhook()));
    	return true;
    }

    public function sendMessageDirect($message)
    {
        if(!$this->provider->isEnable() || $this->provider->getWebhook() === "") return false;
        
        $message = [
                  'username' => $this->provider->getUserName(),
                  'content' => $message,
                ];
        $options = [
                'http' => [
                              'method' => 'POST',
                              'header' => 'Content-Type: application/json',
                              'content' => json_encode($message),
                        ]
                    ];
        $options['ssl']['verify_peer']=false;
        $options['ssl']['verify_peer_name']=false;
        $response = file_get_contents($this->provider->getWebhook(), false, stream_context_create($options));
        return $response;
    }

}
