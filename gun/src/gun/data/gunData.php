<?php
namespace gun\data;

use pocketmine\utils\Config;

class gunData {

	private static $instance;
	
	public function __construct($plugin){
		$datafolder = $plugin::$datafolder;
		self::$instance = $this;
		$this->config = new Config($datafolder. "ar.yml", Config::YAML);
	}
	
	public static function get($name){
		if(self::$instance->config->exists($name)){
			return self::$instance->config->get($name);
		}else{
			return false;
		}
	}
	
	public static function getAll(){
		return self::$instance->config->getAll();
	}
	
	public static function set($name, $data){
		self::$instance->config->set($name, $data);
		self::$instance->config->save();
	}
}
