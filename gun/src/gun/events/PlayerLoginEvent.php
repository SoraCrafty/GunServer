<?php

namespace gun\events;

use pocketmine\level\Position;

class PlayerLoginEvent extends Events{
    
    
    	public function call($ev){
        	$p = $ev->getPlayer();
        	$n = $p->getName();
       		$user = [
			'name' => $n,
        	        'money' => 0,
        	        'kills' => 0,
        	        'deaths' => 0,
        	        'weapons' => [
        			'main' => 'muku_gun',
                	    	'sub' => 'sora_crafty',
                	    	'granade' => '----',
                	    	'knife' => '----',
                	],
        	];
        	$p->userdata = $user;

        	$lv = $this->server->getDefaultLevel();
        	$p->level = $lv;
        	$p->x = 0;
        	$p->y = 5;
        	$p->z = 0;
        	$p->yaw = 0;
    	}
}
