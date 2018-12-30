<?php
namespace gun\data;

use pocketmine\utils\Config;

class npcData {

	private static $instance;
	
	public function __construct($plugin){
		$datafolder = $plugin::$datafolder;
		self::$instance = $this;
		$this->config = new Config($datafolder. "npcData.yml", Config::YAML);
		$this->skin = new Config($datafolder. "skin.serialized", Config::SERIALIZED);
	}
	
	public static function getAll(){
		return self::$instance->config->getAll();
	}
	
	public static function set($name, $data){
		self::$instance->config->set($name, $data);
		self::$instance->config->save();
	}
	
	public static function getSkinAll(){
		return self::$instance->skin->getAll();
	}
	
	public static function setSkin($name, $data){
		self::$instance->skin->set($name, $data);
		self::$instance->skin->save();
	}
}
