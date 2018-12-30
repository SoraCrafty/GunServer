<?php
namespace gun\events;

use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;

class PlayerInteractEvent extends Events {
	
	public function call($ev){
		$player = $ev->getPlayer();
		if(($id = $player->getInventory()->getItemInHand()->getId()) === 278){
			$this->sendSettingMenu($player);
		}elseif($id === 276){
			$this->sendSrMenu($player);
		}
	}
	
	public function sendSettingMenu($player){
		$pk = new ModalFormRequestPacket();
		$pk->formId = 1;
		$content = [ ["type" => "input", "text" => 'gunname', "placeholder" => "", "default" => null],
					["type" => "input", "text" => 'speed', "placeholder" => "", "default" => null],
					["type" => "input", "text" => 'damage', "placeholder" => "", "default" => null],
					["type" => "input", "text" => 'reload', "placeholder" => "", "default" => null],
					["type" => "input", "text" => 'max_ammo', "placeholder" => "", "default" => null],
				];
		$data = [ "type" => "custom_form", "title" => "gunmake", "content" => $content];
		$pk->formData = json_encode($data, JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING | JSON_UNESCAPED_UNICODE);
		$player->dataPacket($pk);
	}
	
		public function sendSrMenu($player){
		$pk = new ModalFormRequestPacket();
		$pk->formId = 2;
		$content = [ ["type" => "input", "text" => 'gunname', "placeholder" => "", "default" => null],
					["type" => "input", "text" => 'range', "placeholder" => "", "default" => null],
					["type" => "input", "text" => 'damage', "placeholder" => "", "default" => null],
					["type" => "input", "text" => 'reload', "placeholder" => "", "default" => null],
					["type" => "input", "text" => 'max_ammo', "placeholder" => "", "default" => null],
				];
		$data = [ "type" => "custom_form", "title" => "Srmake", "content" => $content];
		$pk->formData = json_encode($data, JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING | JSON_UNESCAPED_UNICODE);
		$player->dataPacket($pk);
	}
}
		
