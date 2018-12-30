<?php

namespace gun\events;

use pocketmine\Server;
use pocketmine\math\Vector3;
use pocketmine\level\Location;

abstract class Events {

	protected $listener;
	protected $plugin;
	
	public function __construct($listener){
		$this->listener = $listener;
		$this->plugin = $listener->plugin;
		$this->server = $listener->server;
		$this->schedule = $this->plugin->getScheduler();
	}
	
	public function call($ev){}
	
	public function __toOrgString($pos){
		return implode(",",[$pos->x, $pos->y, $pos->z]);
	}
	
	public function __fromString($str){
		$pos = explode(",", $str);
		return new Vector3(...$pos);
	}
	
	public function sendMessage($p, $message){
		$p->sendMessage($message);
	}
}
