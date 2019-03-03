<?php

namespace gun\weapons;

use pocketmine\Player;

use pocketmine\event\Listener;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\server\DataPacketReceiveEvent;

use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;

use gun\fishing\event\PlayerUseFishRodEvent;

use gun\game\GameManager;

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
		if(!GameManager::getObject()->isGaming()) return true;

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
			if($pk->transactionType === InventoryTransactionPacket::TYPE_USE_ITEM || $pk->transactionType === InventoryTransactionPacket::TYPE_USE_ITEM_ON_ENTITY)
			{
				$this->onEvent(Weapon::EVENT_INTERACT, $event->getPlayer());
			}
		}

		if($pk instanceof LevelSoundEventPacket)
		{
			if($pk->sound === LevelSoundEventPacket::SOUND_ATTACK_NODAMAGE){
				$this->onEvent(Weapon::EVENT_PRE_INTERACT, $event->getPlayer());
			}
		}
	}

	public function onSneak(PlayerToggleSneakEvent $event)
	{
		$this->onEvent(Weapon::EVENT_SNEAK, $event->getPlayer());
	}

	public function onItemHeld(PlayerItemHeldEvent $event)//後々改善したい
	{
		if(!GameManager::getObject()->isGaming()) return true;
		
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

	public function onShootBow(EntityShootBowEvent $event)
	{
		$player = $event->getEntity();
		if($player instanceof Player)
		{
			$this->onEvent(Weapon::EVENT_SHOOTBOW, $player, $event);
		}
	}

	public function onDropItem(PlayerDropItemEvent $event)
	{
		$this->onEvent(Weapon::EVENT_DROP_ITEM, $event->getPlayer(), $event);
	}

	public function onDeath(PlayerDeathEvent $event)
	{
		$player = $event->getPlayer();

		if($player->getLastDamageCause() instanceof EntityDamageByEntityEvent)
		{
			$killer = $player->getLastDamageCause()->getDamager();
			if($killer instanceof Player)
			{
				$this->onEvent(Weapon::EVENT_KILL, $killer, $player);
				$this->onEvent(Weapon::EVENT_DEATH, $player, $killer);
				return true;
			}
		}

		$this->onEvent(Weapon::EVENT_DEATH, $player);
	}

	public function onUseFishRod(PlayerUseFishRodEvent $event)
	{
		$this->onEvent(Weapon::EVENT_USE_FISHROD, $event->getPlayer());
	}

}

