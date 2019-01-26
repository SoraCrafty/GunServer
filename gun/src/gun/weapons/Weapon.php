<?php

namespace gun\weapons;

use pocketmine\item\Item;

use pocketmine\entity\Attribute;

use pocketmine\nbt\tag\CompoundTag;

abstract class Weapon
{
	/*その武器種のID*/
	const WEAPON_ID = "";
	/*武器種の名称*/
	const WEAPON_NAME = "";
	/*Loreに書く数値*/
	const ITEM_LORE = [];

	const TAG_WEAPON = "weapon";
	const TAG_WEAPON_ID = "weapon_id";
	const TAG_TYPE = "type";
	const TAG_BULLET = "bullet";

	const EVENT_INTERACT = "onInteract";
	const EVENT_SNEAK = "onSneak";
	const EVENT_WEAPON_ON = "onWeaponOn";
	const EVENT_WEAPON_OFF = "onWeaponOff";
	const EVENT_MOVE = "onMove";
	const EVENT_SHOOTBOW = "onShootBow";
	const EVENT_DROP_ITEM = "onDropItem";
	const EVENT_DEATH = "onDeath";

	/*Mainクラスのオブジェクト*/
	protected $plugin;
	/*武器の配列*/
	protected $weapons = [];

	public function __construct($plugin)
	{
		$this->plugin = $plugin;

		if(!file_exists($this->plugin->getDataFolder() . static::WEAPON_ID)){
			mkdir($this->plugin->getDataFolder() . static::WEAPON_ID);
		}

		$dir = $this->plugin->getDataFolder() . static::WEAPON_ID . "/";
		foreach(scandir($dir) as $file){
			if($file !== "." and $file !== ".."){
				$data = yaml_parse_file($dir . $file);
				$key = array_keys($data)[0];
				$this->weapons[$key] = $data[$key];
			}
		}
	}

	public function getId()
	{
		return static::WEAPON_ID;
	}

	public function getData($type)
	{
		$data = null;
		if(isset($this->weapons[$type])) $data = $this->weapons[$type];
		return $data;
	}

	public function getDataAll()
	{
		return $this->weapons;
	}

	public function get($type)
	{
		if(!isset($this->weapons[$type])) return null;

		$item = Item::get($this->weapons[$type]["Item_Information"]["Item_Id"], $this->weapons[$type]["Item_Information"]["Item_Damage"], 1);

		$item->setCustomName($this->weapons[$type]["Item_Information"]["Item_Name"]);

		$lore = [];
		$lore[] = "§l§7§n" . static::WEAPON_NAME . "§r";
		foreach (static::ITEM_LORE as $datakey => $data) {
			foreach ($data as $key => $value) {
				$lore[] = "§3" . $value . ":§f" . $this->weapons[$type][$datakey][$key];
			}
		}
		$lore[] = "§f" . $this->weapons[$type]["Item_Information"]["Item_Lore"];
		$item->setLore($lore);

		$nbt = new CompoundTag(self::TAG_WEAPON);
		$nbt->setString(self::TAG_WEAPON_ID, static::WEAPON_ID);
		$nbt->setString(self::TAG_TYPE, $type);
		$item->setNamedTagEntry($nbt);

		return $item;
	}

	public function onInteract($player, $data)
	{

	}

	public function onWeaponOn($player, $data, $args)
	{
		$attribute = $player->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED);
		$attribute->setValue($player->isSprinting() ? ($attribute->getDefaultValue() * 1.3 * $data["Move"]["Move_Speed"]) : $attribute->getDefaultValue() * $data["Move"]["Move_Speed"], false, true);
	}

	public function onWeaponOff($player, $data, $args)
	{
		$attribute = $player->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED);
		$attribute->setValue($player->isSprinting() ? ($attribute->getDefaultValue() * 1.3) : $attribute->getDefaultValue(), false, true);
	}

	public function onSneak($player, $data)
	{

	}

	public function onMove($player, $data)
	{

	}

	public function onShootBow($player, $data, $args)
	{

	}

	public function onDropItem($player, $data, $args)
	{

	}

	public function onDeath($player, $data)
	{
		
	}
}

