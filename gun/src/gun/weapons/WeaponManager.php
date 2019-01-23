<?php

namespace gun\weapons;

class WeaponManager
{
	/*Weaponの配列*/
	private static $class = [];

	public static function init($plugin)
	{
		self::registerWeapon(new AssaultRifle($plugin));
		self::registerWeapon(new SniperRifle($plugin));
		
		$plugin->getServer()->getPluginManager()->registerEvents(new WeaponListener($plugin), $plugin);
	}

	public static function registerWeapon(Weapon $weapon)
	{
		self::$class[$weapon->getId()] = $weapon;
	}

	public static function get($id, $type)
	{
		$weapon = null;

		if(isset(self::$class[$id])){
			$weapon = self::$class[$id]->get($type);
		}

		return $weapon;
	}

	public static function getAllData($id)
	{
		$data = null;

		if(isset(self::$class[$id])){
			$data = self::$class[$id]->getDataAll();
		}

		return $data;
	}

	public static function getIds()
	{
		return array_keys(self::$class);
	}

	public static function getObject($id)
	{
		$object = null;
		if(isset(self::$class[$id])) $object = self::$class[$id];

		return $object;
	}

}

