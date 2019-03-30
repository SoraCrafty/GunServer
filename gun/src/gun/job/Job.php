<?php
namespace gun\job;

use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;

abstract class Job {

	const JOB_ID = '';
	const JOB_DISCRIPTION = '';
	const JOB_NAME = '';
	
	const SKILL_NAME = '';
	const SKILL_CT = 0;
	const SKILL_ITEM_ID = 0;
	const SKILL_ITEM_LORE = [];
	
	const EVENT_INTERACT = "onInteract";
	const EVENT_SNEAK = "onSneak";
	const EVENT_MOVE = "onMove";
	const EVENT_DEATH = "onDeath";
	const EVENT_KILL = "onKill";
	
	const TAG_SKILL = 'skill';
	const TAG_JOB_ID = 'job_id';

	protected $plugin;
	
	public function __construct($plugin){
		$this->plugin = $plugin;
	}
	
	public function getId(){
		return static::JOB_ID;
	}
	
	public function getDescription(){
		return static::JOB_DISCRIPTION;
	}
	
	public function getName(){
		return static::JOB_NAME;
	}
	
	public function getSkillItem(){
		$item = Item::get(static::SKILL_ITEM_ID, 0, 1);
		
		$item->setCustomName(static::SKILL_NAME);

		$lore = [];
		$lore[] = "§l§7§n" . static::SKILL_NAME . "§a | ct : " . static::SKILL_CT ;
		foreach (static::SKILL_ITEM_LORE as $data) {
			$lore[] = $data;
		}
		$item->setLore($lore);

		$nbt = new CompoundTag(self::TAG_SKILL);
		$nbt->setString(self::TAG_JOB_ID, static::JOB_ID);
		$item->setNamedTagEntry($nbt);

		return $item;
	}
	
	public function setup($player){
	}
	
	public function onInteract($player, $event){
	}
	
	public function onSneak($player, $event){
	}
	
	public function onMove($player, $event){
	}
	
	public function onDeath($player, $event){
	}
	
	public function onKill($player, $event){
	}
}
	
	
	
