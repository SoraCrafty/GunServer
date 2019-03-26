<?php
namespace gun\skills;

use gun\skills\ult as ult;

class SkillsManager{
	
	private static $skills[];
	
	public static function init($plugin){
		self::register(new ult\Guard($plugin));
		new SkillsListener($plugin);
	}
	
	public static function register($object){
		self::skills[$object->getID()] = $object;
	}
	
	public static function getObject($id){
		return self::skills[$id];
	}
}
