<?php
namespace gun\npc;

use pocketmine\Player;

use pocketmine\utils\UUID;

use pocketmine\item\Item;

use pocketmine\entity\Skin;
use pocketmine\entity\Entity;

use pocketmine\math\Vector3;

use pocketmine\level\Level;
use pocketmine\level\Location;

use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\PlayerSkinPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\network\mcpe\protocol\SetEntityDataPacket;
use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\network\mcpe\protocol\MobArmorEquipmentPacket;
use pocketmine\network\mcpe\protocol\RemoveEntityPacket;

use pocketmine\network\mcpe\protocol\types\ContainerIds;

class NPC extends Location{

	const NPC_TYPE = 0;

	/*以下のものをLocationから継承している
	int   $x
	int   $y
	int   $z
	float $yaw
	float $pitch
	Level $level
	*/

	/*NPCの名前*/
	private $name;
	/*NPCのサイズ*/
	private $size;
	/*NPCのスキン*/
	private $skin;
	/*NPCの右手に持っているアイテム*/
	private $item_right;
	/*NPCの左手に持っているアイテム*/
	private $item_left;
	/*NPCのヘルメット*/
	private $helmet;
	/*NPCのチェストプレート*/
	private $chestplate
	/*NPCのレギンス*/
	private $leggings;
	/*NPCのブーツ*/
	private $boots;
	/*NPCがプレイヤーの方を向くかどうか*/
	private $doGaze;
	/*NPCのEntityRuntimeId*/
	private $eid;

	public function __construct($name, $size, Skin $skin, Item $item_right, Item $item_left, $helmet, $chestplate, $leggings, $boots, $doGaze, $x, $y, $z, $yaw, $pitch, Level $level)
	{
		parent::__construct($x, $y, $z, $yaw, $pitch, $level);

		$this->name = $name;
		$this->size = $size;
		$this->skin = $skin;
		$this->item_right = $item_right;
		$this->item_left = $item_left;
		$this->helmet = $helmet;
		$this->chestplate = $chestplate;
		$this->leggings = $leggings;
		$this->boots = $boots;
		$this->doGaze = $doGaze;
		$this->eid = Entity::$entityCount++;

		$this->uuid = UUID::fromRandom();
	}

	public function onTouch(Player $player)
	{
		
	}

	public function spawn(){
		foreach($this->level->getPlayers() as $player){
			$this->spawnTo($player);
		}		
	}

	public function spawnTo(Player $player){
		$pk = new AddPlayerPacket();
		$pk->uuid = $this->uuid;
		$pk->username = $this->name;
		$pk->entityRuntimeId = $this->eid;
		$pk->position = new Vector3($this->x, $this->y, $this->z);
		$pk->motion = new Vector3(0, 0, 0);
		$pk->yaw = $this->yaw;
		$pk->pitch = $this->pitch;
		$pk->item = $this->item;
		$pk->metadata =
		[
			Entity::DATA_FLAGS => 
				[
					Entity::DATA_TYPE_LONG, 1 << Entity::DATA_FLAG_ALWAYS_SHOW_NAMETAG ^ 1 << Entity::DATA_FLAG_CAN_SHOW_NAMETAG
				],
			Entity::DATA_NAMETAG => 
				[
					Entity::DATA_TYPE_STRING, $this->name
				],
			Entity::DATA_LEAD_HOLDER_EID => 
				[
					Entity::DATA_TYPE_LONG, -1
				],
			Entity::DATA_SCALE => 
				[
					Entity::DATA_TYPE_FLOAT,$this->size
				]
		];

		$player->dataPacket($pk);

		$this->sendSkinTo($player);

		$this->sendArmorsTo($player);

		$this->sendItem_LeftTo($player);
	}

	public function despawn(){
		foreach($this->level->getPlayers() as $player){
			$this->despawnFrom($player);
		}
	}

	public function despawnFrom(Player $player){
		$pk = new RemoveEntityPacket();
		$pk->entityUniqueId = $this->eid;

		$player->dataPacket($pk);
	}

	//Name関連
	public function setName($name)
	{
		$this->name = $name;
		$this->sendName();
	}

	public function getName(){
		return $this->name;
	}

	public function sendName()
	{
		foreach($this->level->getPlayers() as $player){
			$this->sendNameTo($player);
		}	
	}

	public function sendNameTo(Player $player)
	{
		$pk = new SetEntityDataPacket();
		$pk->entityRuntimeId = $this->eid;
		$pk->metadata =
		[
			Entity::DATA_NAMETAG => 
				[
					Entity::DATA_TYPE_STRING, $this->name
				]
		];

		$player->dataPacket($pk);
	}

	//サイズ関連
	public function setSize($size){
		$this->size = $size;
		$this->sendSize();
	}

	public function getSize(){
		return $this->size;
	}

	public function sendSize(){
		foreach($this->level->getPlayers() as $player){
			$this->sendSizeTo($player);
		}		
	}

	public function sendSizeTo(Player $player){
		$pk = new SetEntityDataPacket();
		$pk->entityRuntimeId = $this->eid;
		$pk->metadata =
		[
			Entity::DATA_SCALE => 
				[
					Entity::DATA_TYPE_FLOAT,$this->size
				]
		];

		$player->dataPacket($pk);
	}

	//スキン関連
	public function setSkin($skin){
		$this->skin = $skin;
		$this->sendSkin();
	}

	public function getSkin(){
		return $this->skin;
	}

	public function sendSkin(){
		foreach($this->level->getPlayers() as $player){
			$this->sendSkinTo($player);
		}		
	}

	public function sendSkinTo(Player $player){
		$pk = new PlayerSkinPacket();
		$pk->uuid = $this->uuid;
		$pk->skin = $this->skin;

		$player->dataPacket($pk);
	}

	//アイテム(右手)関連
	public function setItem_Right(Item $item)
	{
		$this->item_right = $item_right;
		$this->sendItem_Right();
	}

	public function getItem_Right()
	{
		return $this->item_right;
	}

	public function sendItem_Right()
	{
		foreach($this->level->getPlayers() as $player){
			$this->sendItem_RightTo($player);
		}			
	}

	public function sendItem_RightTo(Player $player)
	{
		$pk = new MobEquipmentPacket();
		$pk->entityRuntimeId = $this->eid;
		$pk->item = $this->item_right;
		$pk->inventorySlot = $pk->hotbarSlot = 0;
		$pk->windowId = ContainerIds::INVENTORY;
		$player->dataPacket($pk);
	}

	//アイテム(左手)関連...未実装

	public function setItem_Left(Item $item)
	{
		$this->item_left = $item_left;
		$this->sendItem_Left();
	}

	public function getItem_Left()
	{
		return $this->item_left;
	}

	public function sendItem_Left()
	{
		foreach($this->level->getPlayers() as $player){
			$this->sendItem_LeftTo($player);
		}			
	}

	public function sendItem_LeftTo(Player $player)
	{
		//TODO
	}

	//アーマー関連
	public function setHelmet($helmet)
	{
		$this->helmet = $helmet;
	}

	public function setChestplate($chestplate)
	{
		$this->chestplate = $chestplate;
	}

	public function setLeggings($leggins)
	{
		$this->leggings = $leggings;
	}

	public function setBoots($boots)
	{
		$this->boots = $boots;
	}

	public function getHelmet()
	{
		return $this->helmet;
	}

	public function getChestplate()
	{
		return $this->chestplate;
	}

	public function getLeggings()
	{
		return $this->leggings;
	}

	public function getBoots()
	{
		return $this->boots;
	}

	public function sendArmors()
	{
		foreach($this->level->getPlayers() as $player){
			$this->sendArmorsTo($player);
		}			
	}

	public function sendArmorsTo(Player $player)
	{
		$pk = new MobArmorEquipmentPacket();
		$pk->entityRuntimeId = $this->eid;
		$pk->slots = [$this->helmet, $this->chestplate, $this->leggings, $this->boots];

		$player->dataPacket($pk);
	}

	//プレイヤーの方向を向く処理関連
	public function setDoGaze($doGaze)
	{
		$this->doGaze =$doGaze;
	}

	public function isGazer()
	{
		return $this->doGaze;
	}

	public function gazeAt(Player $player)//https://github.com/TuranicTeam/Altay/blob/master/src/pocketmine/entity/Living.php より引用し改変
	{
		$pk = new MovePlayerPacket();
		$pk->entityRuntimeId = $this->eid;
		$pk->position = $this;//このクラスがLocationを継承しているため

		$horizontal = sqrt(($player->x - $this->x) ** 2 + ($player->z - $this->z) ** 2);
		$vertical = $player->y - $this->y;
		$pk->pitch = -atan2($vertical, $horizontal) / M_PI * 180;

		$xDist = $player->x - $this->x;
		$zDist = $player->z - $this->z;
		$pk->yaw = atan2($zDist, $xDist) / M_PI * 180 - 90;
		if($pk->yaw < 0)
		{
			$pk->yaw += 360.0;
		}

		$pk->headYaw = $pk->yaw;

		$player->dataPacket($pk);
	}

	//EntityRuntimeId関連
	public function getId()
	{
		return $this->eid;
	}

}
