<?php

namespace gun\entity\target;

use pocketmine\level\Level;
use pocketmine\entity\Human;
use pocketmine\nbt\tag\CompoundTag;

class Target extends Human
{

	public function __construct(Level $level, CompoundTag $nbt)
	{
		//$geometryData = file_get_contents(__DIR__ . "/geometry.json");
		//var_dump($geometryData);
		parent::__construct($level, $nbt);
	}

}

