<?php

namespace gun\weapons;

use pocketmine\utils\UUID;

use pocketmine\item\Item;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ByteTag;

abstract class UniqueWeapon extends Weapon
{

	protected $data = [];

	public function __construct($plugin)
	{
		$this->plugin = $plugin;

		$file = $this->plugin->getDataFolder() . 'uniques' . "/" . static::WEAPON_ID . ".yml";
		$this->data = file_exists($file) ? yaml_parse_file($file) : static::DEFAULT_DATA; 

		foreach (static::DEFAULT_DATA as $category => $categoryValue) {
			if(!isset($this->data[$category])) $this->data[$category] = $categoryValue;
		}
	}

	public function save()
	{
		$file = $this->plugin->getDataFolder() . 'uniques' . "/" . static::WEAPON_ID . ".yml";
		if(!file_exists($file)) touch($file);
		yaml_emit_file($file, $this->data, YAML_UTF8_ENCODING);
	}

	public function unset($id)
	{

	}

	public function setData($id, $data)
	{
		$this->data[$id] = $data;
	}

	public function getData($id)
	{
		return $this->data;
	}

	public function getDataAll()
	{
		return $this->data;
	}

	public function get($id = null)
	{
		$item = Item::get($this->data["Item_Information"]["Item_Id"], $this->data["Item_Information"]["Item_Damage"], 1);

		$item->setCustomName($this->data["Item_Information"]["Item_Name"]);

		$lore = [];
		$lore[] = "§l§7§n" . static::WEAPON_NAME . "§r";
		foreach (static::ITEM_LORE as $datakey => $data) {
			foreach ($data as $key => $value) {
				$lore[] = "§3" . $value . ":§f" . $this->data[$datakey][$key];
			}
		}
		$lore[] = "§f" . $this->data["Item_Information"]["Item_Lore"];
		$item->setLore($lore);

		$nbt = new CompoundTag(self::TAG_WEAPON);
		$nbt->setString(self::TAG_WEAPON_ID, static::WEAPON_ID);
		$nbt->setString(self::TAG_TYPE, "");
		$nbt->setString(self::TAG_UNIQUE_ID, UUID::fromRandom()->toString());
		$item->setNamedTagEntry($nbt);
		$item->setNamedTagEntry(new ByteTag("Unbreakable", 1));

		return $item;
	}
	
}