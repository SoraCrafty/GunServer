<?php
namespace gun\events;

use pocketmine\Player;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\Item;

use gun\gameManager;
use gun\data\playerData;

class PlayerDeathEvent extends Events {

	public function __construct($plugin){
		$this->playerdata = playerdata::getPlayerData();
		parent::__construct($plugin);
	}

	public function call($event){
		$event->setKeepInventory(true);
		$player = $event->getPlayer();
    	$this->playerdata->setAccount($player->getName(), 'death', $this->playerdata->getAccount($player->getName())['death'] + 1);
		if(isset($p->shot)) $p->shot = false;
		if($player->getLastDamageCause() instanceof EntityDamageByEntityEvent){
			$killer = $player->getLastDamageCause()->getDamager();
			$item = Item::get(322, 0, 1);
	        $killer->getInventory()->addItem($item);
			$team = $this->plugin->gameManager->getTeam($killer);
			if($team !== false){
				$this->plugin->gameManager->addKillCount($team);
				$this->playerdata->setAccount($killer->getName(), 'kill', $this->playerdata->getAccount($killer->getName())['kill'] + 1);





				
			}
		}
	}
}
