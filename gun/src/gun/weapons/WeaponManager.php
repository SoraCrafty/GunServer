<?php

namespace gun\weapons;

class WeaponManager
{
	/*Weaponの配列*/
	private static $class = [];

	public static function init($plugin)
	{
		self::registerItem(new AssaultRifle($plugin));
		
		$plugin->getServer()->getPluginManager()->registerEvents(new WeaponListener($plugin), $plugin);
	}

	public static function registerItem(Weapon $weapon)
	{
		self::$class[$weapon->getId()] = $weapon;
	}

	public static function get(string $id, string $type)
	{
		$weapon = null;

		if(isset(self::$class[$id])){
			$weapon = self::$class[$id]->get($type);
		}

		return $weapon;
	}

	public static function getObject(string $id)
	{
		$object = null;
		if(isset(self::$class[$id])) $object = self::$class[$id];

		return $object;
	}

}

