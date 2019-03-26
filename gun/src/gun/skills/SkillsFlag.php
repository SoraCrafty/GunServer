<?php
//スキルのフラグ管理
namespace gun\skills;

class SkillsFlag {

	public $plugin;
	private static $instance
	private $flag[];
	
	public function __construct($plugin){
		$this->plugin = $plugin;
		self::$instance = $this;
	}
	
	public function setFlag(Player $player, $id, bool $bool){
		$this->flag[$player->getName()][$id] = $bool;
	}
	
	public function hasFlag(Player $player){
		if(!isset($this->flag[$player->getName()])) return null;
		foreach($this->flag[$player->getName()] as $key => $value){
			if($value){
				$data[] = $key;
			}
		}
	}
	
	public function getFlagById(Player $player, $id){
		if(!isset($this->flag[$player->getName()][$id]) return false;
		return $this->flag[$player->getName()][$id];
	}
	
	public function getFlag(){
		return self::$instance;
	}
}

	
