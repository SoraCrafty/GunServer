<?php

namespace gun\events;

use pocketmine\item\Item;
use pocketmine\Server;

use gun\gameManager;
use gun\scoreboard\scoreboard;
use gun\bossbar\BossBar;

use gun\weapons\WeaponManager;

class PlayerJoinEvent extends Events {
  
  	public function __construct($api){
		parent::__construct($api);
	}

	public function call($event){
		$player = $event->getPlayer();
		$name = $player->getName();

    	$this->plugin->playerManager->setDefaultSpawn($player);
    	$this->plugin->playerManager->sendBaseScoreboard($player);
        
		$player->sendMessage('§bInfo>>§fBattleFront2に参加していただきありがとうございます');
		$player->sendMessage('§bInfo>>§fタップして操作している方は分割コントロールを推奨します');
		$player->sendMessage('§bInfo>>§fDiscordへの参加をお願い致します');

		$event->setJoinMessage(null);
		Server::getInstance()->broadcastPopup('§6>>参加>>§a' .$event->getPlayer()->getName());

		$this->plugin->playerManager->setLobbyInventory($player);
		$this->plugin->playerManager->setDefaultNameTags($player);

		WeaponManager::setPermission($this->plugin, $player, false);
	}
}
