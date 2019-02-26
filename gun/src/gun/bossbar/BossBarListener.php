<?php

namespace gun\bossbar;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;

class BossBarListener implements Listener
{

	/*メインクラスのオブジェクト*/
	private $plugin;

	public function __construct($plugin)
	{
		$this->plugin = $plugin;
	}

	public function onQuit(PlayerQuitEvent $event)
	{
		$player = $event->getPlayer();
		foreach (BossBarManager::getAllObjects() as $bossbar) {
			if($bossbar->isRegistered($player)) $bossbar->unregister($player);
		}
	}

}

