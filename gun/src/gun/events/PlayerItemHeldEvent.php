<?php

namespace gun\events;

use pocketmine\Player;
use gun\data\gunData;
use gun\data\srData;

class PlayerItemHeldEvent extends Events{

	public function call($ev){
		$player = $ev->getPlayer();
		$player->shot = false;
		$item = $ev->getItem();
		unset($player->gun);
		if($name = $item->hasCustomName()){
			switch(($id = $item->getId())){
			case(280):
				if(($gun = gunData::get($item->getName())) !== false) {
					$data = array(
									'speed' => $gun['speed'],
									'damage' => $gun['damage'],
									'reload' => $gun['reload'],
									'max_ammo' => $gun['max_ammo']
								);
					$player->gun = $data;
					if(!isset($player->ammo) or $player->ammo > $gun['max_ammo']) $player->ammo = $gun['max_ammo'];
				}
			break;
			case(261):
				if(($gun = srData::get($item->getName())) !== false) {
					$data = array(
									'range' => $gun['range'],
									'damage' => $gun['damage'],
									'reload' => $gun['reload'],
									'max_ammo' => $gun['max_ammo']
								);
					$player->gun = $data;
					if(!isset($player->ammo) or $player->ammo > $gun['max_ammo']) $player->ammo = $gun['max_ammo'];
				}
			break;
			}
		}
	}
}
								
