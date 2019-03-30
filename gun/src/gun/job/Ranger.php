<?php
namespace gun\job;

use pocketmine\item\Item;

use gun\game\GameManager;

class Ranger extends Job {
	
	const JOB_ID = 'ranger';
	const JOB_DISCRIPTION = '基本的な職業';
	const JOB_NAME = 'レンジャー';
	
	const SKILL_NAME = 'Ranger Cannon';
	const SKILL_CT = 120;
	const SKILL_ITEM_ID = 264;
	const SKILL_ITEM_LORE = ['レンジャー専用キャノン。', 'すべてを貫通するビームを放つ', '火力 : 20, 射程 : 30'];
	
	public function setup($player){
		$item = parent::getSkillItem();
		$player->getInventory()->addItem($item);
	}
	
	public function onInteract($player, $event){
		$item = $player->getInventory()->getItemInHand();
		$tag = $item->getNamedTagEntry(Job::TAG_SKILL);
		if(!is_null($tag) || $tag->getTag(Job::TAG_JOB_ID)->getValue() === self::JOB_ID){
			$this->plugin->getScheduler()->scheduleDelayedTask(new CallBack([$this, "EndCT"], [$player, $item]), self::SKILL_CT * 20);
			$player->getInventory()->removeItem($item);
			$this->shot($player);
		}
	}
	
	public function shot($player){
	}
	
	public function EndCT($player, $item){
		if(GameManager::getObject()->isGaming()){
			$player->getInventory()->addItem($item);
			$player->sendMessage('§l§aスキルのCTが終わりました');
		}
	}
}
	
