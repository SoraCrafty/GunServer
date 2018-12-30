<?php
namespace gun\data;

use pocketmine\utils\Config;

class srData {

	private static $instance;
	
	public function __construct($plugin){
		$datafolder = $plugin::$datafolder;
		self::$instance = $this;
		$this->config = new Config($datafolder. "sr.yml", Config::YAML);
	}
	
	public static function get($name){
		if(self::$instance->config->exists($name)){
			return self::$instance->config->get($name);
		}else{
			return false;
		}
	}
	
	public static function set($name, $data){
		self::$instance->config->set($name, $data);
		self::$instance->config->save();
	}
}
