<?php

namespace gun;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\item\Item;

use gun\data\gunData;
use gun\data\srData;
use gun\data\npcData;

use gun\npcManager;

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
				if(($gun = gunData::get($args[1]))){
					$item = Item::get(280,0,1)->setCustomName($args[1]);
					$lore = array("§a発射レート:".$gun['speed'], "§b火力:".$gun['damage'], "§cリロード:".$gun['reload'], "§d弾数:".$gun['max_ammo']);
					$item->setLore($lore);
					$p->getInventory()->addItem($item);
				}else{
					$p->sendMessage('銃が存在しません');
				}
			break;
			case('sr'):
				if(($gun = srData::get($args[1]))){
					$item = Item::get(261,0,1)->setCustomName($args[1]);
					$lore = array("§a射程:".$gun['range'], "§b火力:".$gun['damage'], "§cリロード:".$gun['reload'], "§d弾数:".$gun['max_ammo']);
					$item->setLore($lore);
					$p->getInventory()->addItem($item);
				}else{
					$p->sendMessage('銃が存在しません');
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
