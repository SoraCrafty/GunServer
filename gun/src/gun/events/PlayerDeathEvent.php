<?php
namespace gun\events;

use pocketmine\Player;
use pocketmine\event\entity\EntityDamageByEntityEvent;

use gun\gameManager;

class PlayerDeathEvent extends Events {
	
	public function call($ev){
		$player = $ev->getPlayer();
		$ev->setKeepInventory(true);
		if(isset($p->shot)) $p->shot = false;
		if($player->getLastDamageCause() instanceof EntityDamageByEntityEvent){
			$killer = $player->getLastDamageCause()->getDamager();
			if(($team = gameManager::getTeam($killer->getName()))){
				gameManager::addKillCount($team);
				$kill = gameManager::getKillCount($team);
				$this->server->broadcastPopup('§aGAME>>§f'.$team.'チームのキル数:'.$kill.'Killです');
				gameManager::toSpawn($player);
			}
		}
	}
}
