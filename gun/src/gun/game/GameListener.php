<?php

namespace gun\game;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\entity\EntityDamageEvent;

use gun\npc\EventNPCTouchEvent;

class GameListener implements Listener
{
	/*ゲームオブジェクト*/
	private $gameObject;

	public function __construct($gameObject)
	{
		$this->gameObject = $gameObject;
	}

	public function onInteract(PlayerInteractEvent $event)
	{
		GameManager::getObject()->onInteract($event);
	}

	public function onEventNPCTouch(EventNPCTouchEvent $event)
	{
		GameManager::getObject()->onEventNPCTouch($event);
	}

	public function onPlayerDeath(PlayerDeathEvent $event)
	{
		GameManager::getObject()->onPlayerDeath($event);
	}

	public function onDamage(EntityDamageEvent $event)
	{
		GameManager::getObject()->onDamage($event);
	}

	public function onRespawn(PlayerRespawnEvent $event)
	{
		GameManager::getObject()->onRespawn($event);
	}

	public function onQuit(PlayerQuitEvent $event)
	{
		GameManager::getObject()->onQuit($event);
	}

}