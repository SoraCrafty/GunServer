<?php
namespace gun\skills;

use pocketmine\item\Item;

use gun\skills\ult as ult;
use gun\skills\SkillsManager as Manager;
use gun\skills\SkillsFlag as Flag;

class Skills{

	private static $instance;
	
	private $skills[];
	
	const SKILL_ID = "";
	
	const TAG_SKILL = "skill";
	const TAG_SKILL_ID = "skill_id";
	const TAG_TYPE = "type";
	const TAG_CT = "ct";
	
	public function __construct($plugin){
		$this->plugin = $plugin;
		self::$instance = $this;
		
	
	public function get($id){
		$skills = Manager::getSkillsManager()->getObject($id);
		if(!isset($skills)) return null;
		
		$item = Item::get($skills::ITEM_ID, $skills::ITEM_DAMAGE, 1);

		$item->setCustomName($skills::ITEM_NAME);

		$lore = [];
		foreach ($skills::ITEM_LORE as $data) {
			$lore[] = $data;
		}
		$lore[] = '§act§f:'.$skills::CT.'秒';
		$item->setLore($lore);

		$nbt = new CompoundTag(self::TAG_SKILL);
		$nbt->setString(self::TAG_SKILL_ID, skills::SKILL_ID);
		$nbt->setString(self::TAG_TYPE, $id);
		$item->setNamedTagEntry($nbt);
		return $item;
	}
	
	public function getID(){
		return static::SKILL_ID;
	}
}
