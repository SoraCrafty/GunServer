<?php
namespace gun\skills\ult;

use gun\skills\SkillsFlag as Flag;

class Guard extends Skills{

	public $plugin;
	const SKILL_ID = "Guard";
	const ITEM_ID = 264;
	const ITEM_DAMAGE = 0;
	const ITEM_NAME = 'Guard';
	const ITEM_LORE = array('防御', 'の', 'skill');
	const CT = 60;
	
	public function __construct($plugin){
		$this->plugin = $plugin;
	}
	
	public function onInteract($event){
		$player = $event->getPlayer();
		Flag::getFlag()->setFlag($player, self::SKILL_ID, true);
		$player->sendMessage('１秒間無敵！');
		$this->plugin->getScheduler()->scheduleDelayedTask(new CallBack([$this, "Kaijo"], [$player]), 20);
	}
	
	public function onDamage($event){
		$player = $event->getEntity();
		if(Flag::getFlag()->getFlagById($player, self::SKILL_ID)){
			$event->setCancelled(true);
		}
	}
	
	public function Kaijo($player){
		Flag::getFlag()->setFlag($player, self::SKILL_ID, false);
	}
	
}
