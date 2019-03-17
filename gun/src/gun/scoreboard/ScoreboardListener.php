<?php
namespace gun\scoreboard;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;

class ScoreboardListener implements Listener{

	private $plugin;

	public function __construct($plugin)
	{
		$this->plugin = $plugin;
	}

	public function onJoin(PlayerJoinEvent $event)
	{
		ScoreboardManager::prepare($event->getPlayer());
	}

}
		
