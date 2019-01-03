<?php
namespace gun\events;

use pocketmine\Player;
use pocketmine\event\entity\EntityDamageByEntityEvent;

use gun\gameManager;
use gun\data\playerData;

class PlayerDeathEvent extends Events {

	public function __construct($plugin){
		$this->playerdata = playerdata::getPlayerData();
	}
	
	public function call($ev){
		$player = $ev->getPlayer();
		$this->playerdata->setAccount($player->getName(), 'death', $this->playerdata->getAccount()['death']++);
		$ev->setKeepInventory(true);
		if(isset($p->shot)) $p->shot = false;
		if($player->getLastDamageCause() instanceof EntityDamageByEntityEvent){
			$killer = $player->getLastDamageCause()->getDamager();
			if(($team = gameManager::getTeam($killer->getName()))){
				gameManager::addKillCount($team);
				$kill = gameManager::getKillCount($team);
				$this->server->broadcastPopup('§aGAME>>§f'.$team.'チームのキル数:'.$kill.'Killです');
				gameManager::toSpawn($player);
				$this->playerdata->setAccount($killer->getName(), 'kill', $this->playerdata->getAccount()['kill']++);
			}
		}
	}
}
