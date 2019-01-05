<?php

namespace gun;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\item\Item;

use gun\data\gunData;
use gun\data\srData;
use gun\data\npcData;

use gun\npcManager;

use gun\weapons\beam;
use gun\weapons\SR;

class Command {

    public function __construct($plugin){
        $this->plugin = $plugin;
        $this->server = $plugin->getServer()->getInstance();
    }

    public function call($sender, $command, $label, $args){
    	if(($p = $sender) instanceof Player){
			if(!isset($args[0]) or !isset($args[1])) return false;
			switch($args[0]){
				case('ar'):
					$ar = beam::get($args[1]);
					if($ar === false)
					{
						$p->sendMessage('銃が存在しません');
					}
					else
					{
						$p->getInventory()->addItem($ar);
					}
					break;
				case('sr'):
					$sr = SR::get($args[1]);
					if($ar === false)
					{
						$p->sendMessage('銃が存在しません');
					}
					else
					{
						$p->getInventory()->addItem($sr);
					}
					break;
				case('npc'):
					npcData::set('shop', array('x' => $sender->x, 'y' => $sender->y, 'z' => $sender->z, 'yaw' => $sender->yaw,'eid' => $args[1],'uuid' => md5(uniqid('', false))));
					
					npcData::setSkin('shop', array( 'id' => $sender->getSkin()->getSkinId(),
											'data' => $sender->getSkin()->getSkinData()
											));
											
					foreach($this->server->getOnlinePlayers() as $p){
						npcManager::removeNPC($p);
						npcManager::addNPC($p);
					}
					$sender->sendMessage('セットしました');
					return true;
					break;
			}
		}
		return true;
	}
}
