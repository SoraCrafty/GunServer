<?php

namespace gun\events;

class PlayerQuitEvent extends Events {

	public function call($event){
		$event->getPlayer()->setSpawn($this->plugin->getServer()->getDefaultLevel()->getSpawnLocation());
	}
	
}
