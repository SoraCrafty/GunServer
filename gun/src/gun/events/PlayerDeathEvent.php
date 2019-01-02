<?php
namespace gun\events;

use pocketmine\Player;
use pocketmine\event\entity\EntityDamageByEntityEvent;

use gun\gameManager;

class PlayerDeathEvent extends Events {
	
	public function call($event){
		$event->setKeepInventory(true);
		$player = $event->getPlayer();

		if(isset($p->shot)) $p->shot = false;

		if($player->getLastDamageCause() instanceof EntityDamageByEntityEvent){
			$killer = $player->getLastDamageCause()->getDamager();
			$team = $this->plugin->gameManager->getTeam($killer);
			if($team !== false){
				$this->plugin->gameManager->addKillCount($team);
			}
		}
	}
}
