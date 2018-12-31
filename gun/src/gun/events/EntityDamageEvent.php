<?php
namespace gun\events;

use pocketmine\Player;
use pocketmine\event\entity\EntityDamageByEntityEvent;

use gun\gameManager;

class EntityDamageEvent extends Events {
	
	public function call($event){
		/*if($event instanceof EntityDamageByEntityEvent)
		{
			$player = $event->getEntity();
			$atacker = $event->getDamager();
			if($player instanceof Player and $atacker instanceof Player){
				$playerteam = gameManager::getTeam($player->getName());
				$atackerteam = gameManager::getTeam($atacker->getName());
				if($playerteam === false or $atackerteam === false or $team === $ateam){
					$event->setCancelled(true);
				}
			}
		}*/
	}
	
}
