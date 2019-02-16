<?php
namespace gun\events;

use pocketmine\Player;
use pocketmine\event\entity\EntityDamageEvent as EntityDamageEventRaw;
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
		if($event->getCause() === EntityDamageEventRaw::CAUSE_FALL)//テスト用のため分割して実装
		{
			$damage = round($event->getBaseDamage() / 5);
			if($damage <= 1) $event->setCancelled(true);
			$event->setBaseDamage(round($damage));
		}
	}
	
}
