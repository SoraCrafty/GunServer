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
		foreach(srData::getAll() as $key => $data){
			$this->SR[] = $key;
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
				$gun = gunData::get(self::$instance->datas[$data]);
				$p->getInventory()->clearAll();
				$item = Item::get(280,0,1)->setCustomName(self::$instance->datas[$data]);
				$lore = array("§a発射レート:".$gun['speed'], "§b火力:".$gun['damage'], "§cリロード:".$gun['reload'], "§d弾数:".$gun['max_ammo']);
				$item->setLore($lore);
				$p->getInventory()->addItem($item);
				$p->sendMessage('銃を選択しました');
				if(isset($p->gun)){
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
		}
	}
	
	public static function touch($pk, $p){
		$pk = new ModalFormRequestPacket();
		$pk->formId = 3;
		$buttons = [];
		var_dump(self::$instance->datas);
		foreach(self::$instance->datas as $name){
			$buttons[] = ['text' => $name];
		}
		$data = [ "type" => "form", "title" => "shop", "content" => '銃を選んでください', "buttons" => $buttons];
		$pk->formData = json_encode($data, JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING | JSON_UNESCAPED_UNICODE);
		$p->dataPacket($pk);
	}
}
