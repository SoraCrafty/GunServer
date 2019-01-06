<?php

namespace gun\events;

use pocketmine\item\Item;
use pocketmine\Player;
use gun\data\playerData;

class PlayerRespawnEvent extends Events{

	public function __construct($api){
		$this->playerData = playerData::getPlayerData();
		parent::__construct($api);
	}

	public function call($event){
		$player = $event->getPlayer();
	if ($player->getInventory()->contains(Item::get(322 , 0)) ){
		$player->getInventory()->removeItem(Item::get(322, 0, 1000));

	}

	}


}