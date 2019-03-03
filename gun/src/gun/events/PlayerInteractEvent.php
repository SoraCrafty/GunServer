<?php
namespace gun\events;

use pocketmine\network\mcpe\protocol\LevelSoundEventPacket; 
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\item\Item;

class PlayerInteractEvent extends Events {

	public function call($event){
		$player = $event->getPlayer();
		$id = $player->getInventory()->getItemInHand()->getId();
		$block = $event->getBlock()->getID();

		if($id == 322 && $block == 0){
			$player->getInventory()->removeItem(Item::get(322, 0, 1));
			$player->addEffect(new EffectInstance(Effect::getEffect(10), 20 * 3, 3, false));
		}
		
	}
}
		
