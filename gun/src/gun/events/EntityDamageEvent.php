<?php
namespace gun\events;

use pocketmine\Player;
use pocketmine\event\entity\EntityDamageByEntityEvent;

use gun\gameManager;

class EntityDamageEvent extends Events {
	
	public function call($ev){
		if($ev instanceof EntityDamageByEntityEvent){
			$player = $ev->getEntity();
			$atacker = $ev->getDamager();
			if($player instanceof Player and $atacker instanceof Player){
				if(!gameManager::getTeam($player->getName()) or !gameManager::getTeam($atacker->getName())){
					$ev->setCancelled(true);
				}
			}
		}
	}
}
