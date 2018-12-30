<?php
namespace gun\events;

use pocketmine\Player;

use gun\weapons\SR;
use gun\data\srData;

class EntityShootBowEvent extends Events {

	public function __construct($api){
		parent::__construct($api);
		$this->SR = new SR($api);
	}
	
	public function call($ev){
		$player = $ev->getEntity();
		$ev->setCancelled();
		if($player instanceof Player){
			if(!$player->reloading and ($gun = $player->gun) !== null){
				$this->SR->shot($player,$gun);
			}
		}
	}
}
