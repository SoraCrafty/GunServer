<?php
namespace gun\job;

class JobManager {
	
	private static $class = [];
	
	public static function init($plugin){
		self::registerJob(new Ranger($plugin));
		$plugin->getServer()->getPluginManager()->registerEvents(new JobListener($plugin), $plugin);
	}
	
	public static function registerJob(Job $job){
		self::$class[$job->getId()] = $job;
	}
	
	public static function setup($id, $player){
		if(!isset(self::$class[$id])) return false;
		self::$class[$id]->setup($player);
		$tag = $player->getNamedTag();
		$tag = "[{$id}]{$tag}";
		$player->setNameTag($tag);
    		$player->setDisplayName($tag);
	}
	
	public static function getDescription($id){
		$description = null;
		if(isset(self::$class[$id])){
			$description = self::$class[$id]->getDescription();
		}
		return $description;
	}
	
	public static function getAllId(){
		return array_keys(self::$class);
	}
	
	public static function getAllName(){
		$name = array();
		foreach(self::$class as $Object){
			$name[] = $Object->getName();
		}
		return $name;
	}
	
	public static function getObject($id){
		$class = null;
		if(isset(self::$class[$id])){
			$class = self::$class[$id];
		}
		return $class;
	}
}
	
	

