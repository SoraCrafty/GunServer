<?php

namespace gun\bossbar;

use pocketmine\plugin\Plugin;
use pocketmine\scheduler\Task;

class BossBarTask extends Task{

	private $api;

	public function __construct($api){
		$this->api = $api;
	}

	public function onRun(int $currentTick){
		if($this->api->isVisivle())
		{
			$this->api->show();
		}
	}

}