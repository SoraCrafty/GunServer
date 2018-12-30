<?php
namespace gun\form;

use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;

use gun\data\srData;
use gun\data\gunData;

class formManager {

	private static $instance;

	public function __construct(){
		self::$instance = $this;
		foreach(gunData::getAll() as $key => $data){
			$this->datas[] = $key;
		}
	}

	public static function receive($pk, $p){
		$data = json_decode($pk->formData);
		if(!isset($data)) return false;
		foreach($data as $value){
			if(!isset($value)) return false;
		}
		switch($pk->formId){
			case(1):
				$gun = array(
								'speed' => $data[1],
								'damage' => $data[2],
								'reload' => $data[3],
								'max_ammo' => $data[4],
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
							);
				srData::set($data[0], $gun);
				$p->sendMessage($data[0].'追加しました');
			break;
			case(3):
				$name = array_slice(self::$instance->datas,$data,1);
				$gun = gunData::get($name);
				$p->getInventory()->clearAll();
				$item = Item::get(280,0,1)->setCustomName($name);
				$lore = array("§a発射レート:".$gun['speed'], "§b火力:".$gun['damage'], "§cリロード:".$gun['reload'], "§d弾数:".$gun['max_ammo']);
				$p->getInventory()->addItem($item);
				$p->sendMessage('銃を追加しました');
			break;
		}
	}
	
	public static function touch($pk, $p){
		$pk = new ModalFormRequestPacket();
		$pk->formId = 3;
		$buttons = [];
		foreach(self::$instance->datas as $name => $data){
			$buttons[] = ['text' => $name];
		}
		$data = [ "type" => "form", "title" => "shop", "content" => '銃を選んでください', "buttons" => $buttons];
		$pk->formData = json_encode($data, JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING | JSON_UNESCAPED_UNICODE);
		$p->dataPacket($pk);
	}
}
