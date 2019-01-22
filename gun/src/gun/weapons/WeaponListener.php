<?php

namespace gun\weapons;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\server\DataPacketReceiveEvent;

use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;

class WeaponListener implements Listener
{
	/*Mainクラスのオブジェクト*/
	public $plugin;

	public function __construct($plugin)
	{
		$this->plugin = $plugin;
	}

	public function onEvent($eventname, $player, ...$args)
	{
		if(!$this->plugin->gameManager->isGaming()) return true;

		$weapon = $player->getInventory()->getItemInHand();

		$tag = $weapon->getNamedTagEntry(Weapon::TAG_WEAPON);
		if(!is_null($tag))
		{
			$object = WeaponManager::getObject($tag->getTag(Weapon::TAG_WEAPON_ID)->getValue());
			$data = $object->getData($tag->getTag(Weapon::TAG_TYPE)->getValue());
			if(!is_null($data))$object->$eventname($player, $data, $args);
		}
	}

	/*public function onInteract(PlayerInteractEvent $event)
	{
		$this->onEvent(Weapon::EVENT_INTERACT, $event->getPlayer(), $event);
	}*/

	public function onPacketReceive(DataPacketReceiveEvent $event)
	{
		$pk = $event->getPacket();

		if($pk instanceof InventoryTransactionPacket)
		{
			$this->onEvent(Weapon::EVENT_INTERACT, $event->getPlayer());
		}

		if($pk instanceof LevelSoundEventPacket)
		{
			if($pk->sound === LevelSoundEventPacket::SOUND_ATTACK_NODAMAGE){
				$this->onEvent(Weapon::EVENT_INTERACT, $event->getPlayer());
			}
		}
	}

	public function onSneak(PlayerToggleSneakEvent $event)
	{
		$this->onEvent(Weapon::EVENT_SNEAK, $event->getPlayer());
	}

	public function onItemHeld(PlayerItemHeldEvent $event)//後々改善したい
	{
		if(!$this->plugin->gameManager->isGaming()) return true;
		
		$player =  $event->getPlayer();

		$item_off = $player->getInventory()->getItemInHand();
		$item_on = $event->getItem();

		$tag = $item_off->getNamedTagEntry(Weapon::TAG_WEAPON);
		if(!is_null($tag))
		{
			$eventname = Weapon::EVENT_WEAPON_OFF;
			$object = WeaponManager::getObject($tag->getTag(Weapon::TAG_WEAPON_ID)->getValue());
			$data = $object->getData($tag->getTag(Weapon::TAG_TYPE)->getValue());
			if(!is_null($data))$object->$eventname($player, $data, $event->getSlot());
		}

		$tag = $item_on->getNamedTagEntry(Weapon::TAG_WEAPON);
		if(!is_null($tag))
		{
			$eventname = Weapon::EVENT_WEAPON_ON;
			$object = WeaponManager::getObject($tag->getTag(Weapon::TAG_WEAPON_ID)->getValue());
			$data = $object->getData($tag->getTag(Weapon::TAG_TYPE)->getValue());
			if(!is_null($data))$object->$eventname($player, $data, $event->getSlot());
		}
	}

	public function onMove(PlayerMoveEvent $event)
	{
		$this->onEvent(Weapon::EVENT_MOVE, $event->getPlayer());
	}

}

