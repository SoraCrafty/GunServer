<?php

namespace gun\server;

use gun\Callback;
use gun\provider\MainSettingProvider;

class RebootManager{

	private $plugin;
	private $rebootCount = 0;

	public function __construct($plugin)
	{
		$this->plugin = $plugin;
		$this->rebootCount = MainSettingProvider::get()->getRebootCount();
	}

	public function getRebootCount()
	{
		return $this->rebootCount;
	}

	public function advanceRebootCount()
	{
		$this->rebootCount--;
		if($this->rebootCount <= 0) $this->rebootTask(11);
	}

	public function rebootTask($progress)
	{
		$progress--;

		if($progress === 1)
		{
			$this->plugin->getServer()->broadcastMessage("§bSystem>>§f再起動を開始します");
			$this->plugin->getScheduler()->scheduleDelayedTask(new Callback([$this, 'rebootTask'], [$progress]), 20);
			return true;
		}

		if($progress === 0)
		{
			$this->plugin->getServer()->shutdown();
			return true;
		}

		$this->plugin->getServer()->broadcastMessage("§bSystem>>§f再起動まであと{$progress}秒");
		$this->plugin->getScheduler()->scheduleDelayedTask(new Callback([$this, 'rebootTask'], [$progress]), 20);
		return true;
	}

}