<?php

namespace gun;

use pocketmine\scheduler\Task;

class RebootTask extends Task{

	const PERIOD = 60 * 60;

	private $plugin;
	private $count = self::PERIOD;

	public function __construct($plugin)
	{
		$this->plugin = $plugin;
	}

	public function onRun($currentTick){
		$this->count--;

		if($this->count === 60 * 10){
			$this->plugin->getServer()->broadcastMessage("§bSystem>>§f再起動まで残り10分です");
			return true;
		}

		if($this->count === 60 * 5){
			$this->plugin->getServer()->broadcastMessage("§bSystem>>§f再起動まで残り5分です");
			return true;
		}

		if($this->count === 60){
			$this->plugin->getServer()->broadcastMessage("§bSystem>>§f再起動まで残り1分です");
			return true;
		}

		if($this->count === 0)
		{
			$this->plugin->getServer()->broadcastMessage("§bSystem>>§f再起動を開始します");
			return true;
		}

		if($this->count === -1)
		{
			$this->plugin->getServer()->shutdown();
			return true;
		}

		if($this->count <= 10)
		{
			$this->plugin->getServer()->broadcastMessage("§bSystem>>§f再起動まであと{$this->count}秒");
			return true;
		}

	}

}
