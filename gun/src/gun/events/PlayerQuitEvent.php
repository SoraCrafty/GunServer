<?php

namespace gun\events;

use pocketmine\Server;
use pocketmine\Player;

class PlayerQuitEvent extends Events {

	public function call($event){
		$event->setQuitMessage(null);
		$this->plugin->getServer()->broadcastPopup('§b退出>>'.$event->getPlayer()->getName().'さん');
		$event->getPlayer()->setSpawn($this->plugin->getServer()->getDefaultLevel()->getSpawnLocation());
	}
	
}
