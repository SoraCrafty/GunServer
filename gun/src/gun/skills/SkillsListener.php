<?php
namespace gun\skills;

use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;

use gun\skills\SkillsManager as Manager;
use gun\skills\SkillsFlag as Flag;

class SkillsListener extends Skills implements Listener {

	public function __construct($plugin){
		$this->plugin = $plugin;
		$this->plugin->getServer()->getPluginManager()->registerEvents($this, $this->plugin);
	}
	
	public function onInteract(PlayerInteractEvent $event){
		$player = $event->getPlayer();
		$skill = $player->getInventory()->getItemInHand();
		$tag = $skill->getNamedTagEntry(parent::TAG_SKILL);
		if(!is_null($tag)){
			Manager::getObject($tag->getTag(parent::TAG_SKILL_ID))->onInteract($event);
		}
	}
	
	public function onDamage(EntityDamageEvent $event){
		if($event instanceof EntityDamageByEntityEvent){
			if($event->getEntity() instanceof Player){
				$flag = Flag::getFlag()->hasFlag($event->getEntity());
				if(is_null($flag)) return false;
				foreach($flag as $id){
					Manager::getObject($flag)->onDamage($event);
				}
			}
		}
	}
}
