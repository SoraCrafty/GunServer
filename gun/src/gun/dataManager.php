<?php
namespace gun;

use pocketmine\utils\Config;
use gun\data as data;

class dataManager {

	private static $instance;
	
	public function __construct($plugin){
		self::$instance = $this;
		$this->player = new data\playerData($plugin);
	}
}
