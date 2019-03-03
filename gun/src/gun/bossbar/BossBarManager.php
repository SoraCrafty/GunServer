<?php

namespace gun\bossbar;

class BossBarManager
{

	private static $objects = [];

	public static function init($plugin)
	{
		$plugin->getScheduler()->scheduleRepeatingTask(new BossBarTask(), 20);
		$plugin->getServer()->getPluginManager()->registerEvents(new BossBarListener($plugin), $plugin);
	}

	public static function register(BossBar $bossbar)
	{
		self::$objects[$bossbar->getEid()] = $bossbar;
	}

	public static function getAllObjects()
	{
		return self::$objects;
	}

}