<?php
namespace gun;

use pocketmine\utils\Config;
use gun\data as data;

class dataManager {

	private static $instance;
	
	public function __construct($plugin){
		self::$instance = $this;
		$this->npc = new data\npcData($plugin);
		$this->gun = new data\gunData($plugin);
		$this->sr = new data\srData($plugin);
		$this->player = new data\playerData($plugin);
	}
}
