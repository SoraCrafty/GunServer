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

    public function sendMessage($message, $channel = "server_chat")
    {
    	if(!$this->provider->isEnable() || $this->provider->getWebhook($channel) === "") return false;

		$content = [
				  'username' => $this->provider->getUserName(),
				  'content' => $message,
				];
    	$this->plugin->getServer()->getAsyncPool()->submitTask(new AsyncSendTask($content, $this->provider->getWebhook($channel)));
    	return true;
    }

    public function sendConvertedMessage($message, $channel = "server_chat")
    {
        if(!$this->provider->isEnable() || $this->provider->getWebhook($channel) === "") return false;
        
        $message = str_replace(["§0", "§1", "§2", "§3", "§4", "§5", "§6", "§7", "§8", "§9", "§a", "§b", "§c", "§d", "§e", "§f", "§k", "§l", "§m", "§n", "§o", "§r"], "", $message);

        $content = [
                  'username' => $this->provider->getUserName(),
                  'content' => $message,
                ];
        $this->plugin->getServer()->getAsyncPool()->submitTask(new AsyncSendTask($content, $this->provider->getWebhook($channel)));
        return true;
    }

    public function sendMessageDirect($message, $channel = "server_chat")
    {
        if(!$this->provider->isEnable() || $this->provider->getWebhook($channel) === "") return false;
        
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
        $response = file_get_contents($this->provider->getWebhook($channel), false, stream_context_create($options));
        return $response;
    }

}
