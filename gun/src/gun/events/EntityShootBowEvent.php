<?php
namespace gun\events;

use pocketmine\Player;

use gun\game\GameManager;

class EntityShootBowEvent extends Events {

	public function call($event)
	{
		if(!GameManager::getObject()->isGaming()) $event->setCancelled(true);
	}

}
