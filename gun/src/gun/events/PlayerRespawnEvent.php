<?php

namespace gun\events;

use pocketmine\item\Item;
use pocketmine\Player;

class PlayerRespawnEvent extends Events{

	public function call($event){
		$player = $event->getPlayer();
	if ($player->getInventory()->contains(Item::get(322 , 0)) ){
		$player->getInventory()->removeItem(Item::get(322, 0, 1000));

	}

	}


}