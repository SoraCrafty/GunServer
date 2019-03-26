<?php
namespace gun\job;

use pocketmine\item\Item;

class Ranger extends Job {
	
	const JOB_ID = 'ranger';
	const JOB_DISCRIPTION = '基本的な職業';
	const JOB_NAME = 'レンジャー';
	
	public function setup($player){
		$player->getInventory()->addItem(Item::get(264,0,1));
	}
	
	public function onInteract($player, $event){
		$item = $player->getInventory()->getItemInHand();
		if($item->getId() === 264){
			$player->sendMessage('うんこ');
		}
	}
}
	
