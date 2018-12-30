<?php
namespace gun;

use pocketmine\entity\Entity;
use pocketmine\entity\Skin;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\RemoveEntityPacket;
use pocketmine\math\Vector3;

use gun\data\npcData;

class npcManager {

	private static $instance;

	public function __construct($plugin){
		self::$instance = $this;
		$this->plugin = $plugin;
		$this->server = $plugin->getServer();
		$this->npc = npcData::getAll();
		$this->skin = npcData::getSkinAll();
		$this->uuid = [];
	}

	public static function addNPC($player){
		foreach(self::$instance->npc as $name => $data){
			$pk = self::$instance->summon($name,new Vector3($data['x'],$data['y'],$data['z']),$data['eid'],$data['yaw'],$data['uuid'],$player);
			$player->dataPacket($pk);
		}
	}
	
	private function summon($type,$pos,$eid,$yaw,$uuiid,$player){
		$pk = new AddPlayerPacket();
		$uuid = \pocketmine\utils\UUID::fromstring($uuiid,1);
		self::$instance->uuid[$eid] = $uuid;
		$pk->uuid = $uuid;
		$pk->username = $type;
		$pk->entityRuntimeId = (int)$eid;
		$pk->position = $pos;
		$pk->yaw = $yaw;
		$name = $type;
		$flags = (
			(1 << Entity::DATA_FLAG_CAN_SHOW_NAMETAG) |
			(1 << Entity::DATA_FLAG_ALWAYS_SHOW_NAMETAG) |
			(1 << Entity::DATA_FLAG_NO_AI)
		);
		$pk->metadata = [ 
		                                Entity::DATA_FLAG_INVISIBLE => [Entity::DATA_TYPE_BYTE, 0],
						Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 1],
						Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING,$name],
						Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, $flags],
		];
		$pk->item = Item::get(0,0,0);
		self::$instance->server->updatePlayerListData($uuid, $pk->entityRuntimeId, $type, new Skin(self::$instance->skin[$type]['id'],self::$instance->skin[$type]['data']),$type,array($player));
		return $pk;
	}
	
	public static function removeNPC($player){
		foreach(self::$instance->npc as $data){
			$pk2 = new RemoveEntityPacket();
			$pk2->entityUniqueId = intval($data['eid']);
			self::$instance->server->removePlayerListdata(self::$instance->uuid[$data['eid']], array($player));
			$player->dataPacket($pk2);
		}
	}
}
