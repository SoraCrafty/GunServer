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

		self::registerWeapon(new HandGun($plugin));
		
		$plugin->getServer()->getPluginManager()->registerEvents(new WeaponListener($plugin), $plugin);
	}

	public static function close()
	{
		foreach (self::$class as $weaponObject) {
			$weaponObject->save();
		}
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

	public static function unset($type, $id)
	{
		self::$class[$type]->unset($id);	
	}

	public static function setData($id, $type, $data)
	{
		self::$class[$id]->setData($type, $data);		
	}

	public static function getData($id, $type)
	{
		$data = null;

		if(isset(self::$class[$id])){
			$data = self::$class[$id]->getData($type);
		}

		return $data;		
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

	public static function getNames()
	{
		$names = [];
		foreach (self::$class as $value) {
			$names[] = $value->getName();
		}	
		return $names;
	}

	public static function getObject($id)
	{
		$object = null;
		if(isset(self::$class[$id])) $object = self::$class[$id];

		return $object;
	}

	public static function getName($id)
	{
		$name = null;

		if(isset(self::$class[$id])){
			$name = self::$class[$id]::WEAPON_NAME;
		}

		return $name;	
	}

}

