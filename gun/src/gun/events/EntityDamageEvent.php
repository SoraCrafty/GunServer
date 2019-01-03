<?php
namespace gun\events;

use pocketmine\Player;
use pocketmine\event\entity\EntityDamageByEntityEvent;

use gun\gameManager;

class EntityDamageEvent extends Events {
	
	public function call($event){
		if($event instanceof EntityDamageByEntityEvent)
		{
			if(!$this->plugin->gameManager->isGaming())
			{
				$event->setCancelled(true);
			}
			else{
				$player = $event->getEntity();
				$atacker = $event->getDamager();
				if($player instanceof Player and $atacker instanceof Player){
					$playerteam = $this->plugin->gameManager->getTeam($player);
					$atackerteam = $this->plugin->gameManager->getTeam($atacker);
					if($playerteam === false || $atackerteam === false || $playerteam === $atackerteam){
						$event->setCancelled(true);
					}
				}
			}
		}
	}
	
}
