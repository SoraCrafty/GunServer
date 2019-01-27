<?php
namespace gun\events;

use pocketmine\Player;

class EntityShootBowEvent extends Events {

	public function call($event)
	{
		if(!$this->plugin->gameManager->isGaming()) $event->setCancelled(true);
	}

}
