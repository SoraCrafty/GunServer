<?php
namespace gun\events;

use pocketmine\Player;
use pocketmine\event\entity\EntityDamageEvent as EntityDamageEventRaw;
use pocketmine\event\entity\EntityDamageByEntityEvent;

use gun\game\GameManager;

class EntityDamageEvent extends Events {
	
	public function call($event){
        if($event instanceof EntityDamageByEntityEvent)
        {
            if(!GameManager::getObject()->isGaming())
            {
                $event->setCancelled(true);
            }
        }
		elseif($event->getCause() === EntityDamageEventRaw::CAUSE_FALL)//テスト用のため分割して実装
		{
			$damage = round($event->getBaseDamage() / 5);
			if($damage <= 1) $event->setCancelled(true);
			$event->setBaseDamage(round($damage));
		}
	}
	
}
