<?php

namespace gun\events;

use pocketmine\Server;
use pocketmine\Player;

class PlayerQuitEvent extends Events {

	public function call($event){
		$event->setQuitMessage(null);
		$this->plugin->getServer()->broadcastPopup('§6>>退出>>§a' .$event->getPlayer()->getName());
		$event->getPlayer()->setSpawn($this->plugin->getServer()->getDefaultLevel()->getSpawnLocation());
		$this->plugin->discordManager->sendMessage('**❌' . $event->getPlayer()->getName() . 'がログアウトしました** ' . '(' . (count($this->plugin->getServer()->getOnlinePlayers()) - 1) . '/' . $this->plugin->getServer()->getMaxPlayers() . ')');
	}
	
}
