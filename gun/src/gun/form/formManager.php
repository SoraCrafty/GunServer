<?php
namespace gun\form;

use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;

use gun\weapons\{ beam, SR };
use gun\data\srData;
use gun\data\gunData;

class formManager {

	private static $instance;

	public function __construct(){
		self::$instance = $this;
		foreach(gunData::getAll() as $key => $data){
			$this->datas[] = array('name' => $key, 'price' => $data['price']);
		}
		foreach(srData::getAll() as $key => $data){
			$this->SR[] = array('name' => $key, 'price' => $data['price'];
		}
	}

	public static function receive($pk, $p){
		$data = json_decode($pk->formData);
		if(!isset($data)) return false;
		/*foreach($data as $value){
			if(!isset($value)) return false;
		}*/
		switch($pk->formId){
			case(1):
				$gun = array(
								'speed' => $data[1],
								'damage' => $data[2],
								'reload' => $data[3],
								'max_ammo' => $data[4],
								'price' => $data[5]
							);
				gunData::set($data[0], $gun);
				$p->sendMessage($data[0].'追加しました');
			break;
			case(2):
				$gun = array(
								'range' => $data[1],
								'damage' => $data[2],
								'reload' => $data[3],
								'max_ammo' => $data[4],
								'price' => $data[5]
							);
				srData::set($data[0], $gun);
				$p->sendMessage($data[0].'追加しました');
			break;
			case(3):
				switch($data){
					case(0):
						self::$instance->sendARShopForm($p);
						break;
					case(1):
						self::$instance->sendSRShopForm($p);
						break;
				}
				break;
			case(4):
				$p->getInventory()->clearAll();
				$item = beam::get(self::$instance->datas[$data]);
				$p->getInventory()->addItem($item);
				$p->sendMessage('銃を選択しました');
				if(isset($p->gun)){
					$gun = gunData::get(self::$instance->datas[$data]);
					$data = array(
									'speed' => $gun['speed'],
									'damage' => $gun['damage'],
									'reload' => $gun['reload'],
									'max_ammo' => $gun['max_ammo']
					);
					$p->gun = $data;
					if(!isset($p->ammo) or $p->ammo > $gun['max_ammo']) $p->ammo = $gun['max_ammo'];
				}
			break;
			case(5):
				$p->getInventory()->clearAll();
				$item = SR::get(self::$instance->SR[$data]);
				if(!$item) return $p->sendMessage('error');
				$p->getInventory()->addItem($item);
				$p->getInventory()->addItem(Item::get(262));
				$p->sendMessage('銃を選択しました');
				if(isset($p->gun)){
					$gun = srData::get(self::$instance->SR[$data]);
					$data = array(
									'range' => $gun['range'],
									'damage' => $gun['damage'],
									'reload' => $gun['reload'],
									'max_ammo' => $gun['max_ammo']
					);
					$p->gun = $data;
					if(!isset($p->ammo) or $p->ammo > $gun['max_ammo']) $p->ammo = $gun['max_ammo'];
				}
			break;
		}
	}
	
	public static function touch($packet, $p){
		$pk = new ModalFormRequestPacket();
		$pk->formId = 3;
		$buttons = [['text' => 'AR'],
				     ['text' => 'SR']];
		$data = [ "type" => "form", "title" => "shop", "content" => '銃を選んでください', "buttons" => $buttons];
		$pk->formData = json_encode($data, JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING | JSON_UNESCAPED_UNICODE);
		$p->dataPacket($pk);
	}
	
	public function sendARShopForm($p){
		$pk = new ModalFormRequestPacket();
		$pk->formId = 4;
		$buttons = [];
		foreach($this->datas as $data){
			$buttons[] = ['text' => "{$data['name']}{$data['price']}円"];
		}
		$data = [ "type" => "form", "title" => "shop", "content" => '銃を選んでください', "buttons" => $buttons];
		$pk->formData = json_encode($data, JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING | JSON_UNESCAPED_UNICODE);
		$p->dataPacket($pk);
	}
	
	public function sendSRShopForm($p){
		$pk = new ModalFormRequestPacket();
		$pk->formId = 5;
		$buttons = [];
		foreach($this->SR as $data){
			$buttons[] = ['text' =>  "{$data['name']}{$data['price']}円"];
		}
		$data = [ "type" => "form", "title" => "shop", "content" => '銃を選んでください', "buttons" => $buttons];
		$pk->formData = json_encode($data, JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING | JSON_UNESCAPED_UNICODE);
		$p->dataPacket($pk);
	}
}
