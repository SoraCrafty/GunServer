<?php

namespace gun\events;

use pocketmine\item\Item;
use pocketmine\Server;


use gun\gameManager;
use gun\npcManager;
use gun\data\gunData;
use gun\data\playerData;
use gun\scoreboard\scoreboard;
use gun\bossbar\BossBar;

use gun\weapons\beam;

class PlayerJoinEvent extends Events {
  
  	public function __construct($api){
		$this->playerData = playerData::getPlayerData();
		parent::__construct($api);
	}

	public function call($event){
		$player = $event->getPlayer();
    		$name = $player->getName();

        $player->sendMessage('§b--------------------------------------------------');
		$player->sendMessage('§bInfo>>§fBattleFront2に参加していただきありがとうございます');
		$player->sendMessage('§bInfo>>§fリロードはスニークして地面タッチです');
		$player->sendMessage('§bInfo>>§f試合開始時のリロード忘れに注意してください');
		$player->sendMessage('§b--------------------------------------------------');
		$event->setJoinMessage(null);
		Server::getInstance()->broadcastPopup('§b参加>>'.$event->getPlayer()->getName().'さん');
		$this->setWeapons($player);		
    		$this->playerData->getAccount($name) ?: $this->playerData->createAccount($name);
		scoreboard::getScoreBoard()->showThisServerScoreBoard($player);
		npcManager::addNPC($player);

		/*途中参加のときの処理(?)*/
		if($this->plugin->gameManager->isGaming())
		{
			$team = $this->plugin->gameManager->getTeam($player);
			if($team === false)
			{
				$this->plugin->gameManager->lotteryTeam($player);
				$team = $this->plugin->gameManager->getTeam($player);
			}
			$this->plugin->gameManager->setSpawn($player, $team);
			$this->plugin->gameManager->gotoStage($player, $team);
			$this->plugin->gameManager->setNameTags($player, $team);
		}

	}
	
	public function setWeapons($p){
		/*if(($inv = $p->getInventory()) !== null){
    		$weapons = $p->userdata['weapons'];
    		$m = $weapons['main'];
    		$s = $weapons['sub'];
    		$g = $weapons['granade'];
    		$k = $weapons['knife'];
		}
		$item = Item::get(280,0,1)->setCustomName('UziWaterPistol');
		$gun = gunData::get('UziWaterPistol');
		$lore = array("§a発射レート:".$gun['speed'], "§b火力:".$gun['damage'], "§cリロード:".$gun['reload'], "§d弾数:".$gun['max_ammo']);
		$item->setLore($lore);
		$p->getInventory()->addItem($item);*/
		//$p->getInventory()->setContents([beam::get("UMP45")]);
    }
}
