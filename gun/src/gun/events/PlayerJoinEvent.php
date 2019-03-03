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
		
		if($player->isOP()){
			$player->setDisplayName("§b☆ §r{$name}");
				$player->setNameTag("§b☆ §r{$name}");
				return false;
		}

    	$player->setSpawn($this->plugin->getServer()->getDefaultLevel()->getSpawnLocation());
    	
        $player->sendMessage('§b--------------------------------------------------');
		$player->sendMessage('§bInfo>>§fBattleFront2に参加していただきありがとうございます');
		$player->sendMessage('§bInfo>>§fリロードはスニークして地面タッチです');
		$player->sendMessage('§bInfo>>§fタップして操作している方は分割コントロールを推奨します');
		$player->sendMessage('§b--------------------------------------------------');
		$event->setJoinMessage(null);
		Server::getInstance()->broadcastPopup('§b参加>>'.$event->getPlayer()->getName().'さん');

		$this->plugin->playerManager->setLobbyInventory($player);

		$this->plugin->discordManager->sendMessage('**⭕' . $player->getName() . 'がログインしました** ' . '(' . count($this->plugin->getServer()->getOnlinePlayers()) . '/' . $this->plugin->getServer()->getMaxPlayers() . ')');
	}
}
