<?php

namespace gun\entity\target;

use pocketmine\level\Level;
use pocketmine\entity\Human;
use pocketmine\Player;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteArrayTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;

class Target extends Human
{

	public function __construct(Level $level, CompoundTag $nbt)
	{
		$geometryData = file_get_contents(__DIR__ . "/geometry.json");
		$path = __DIR__ . "/skin.png";
		$img = @imagecreatefrompng($path);
		$bytes = '';
		$l = (int) @getimagesize($path)[1];
		for ($y = 0; $y < $l; $y++) {
		    for ($x = 0; $x < 64; $x++) {
		        $rgba = @imagecolorat($img, $x, $y);
		        $a = ((~((int)($rgba >> 24))) << 1) & 0xff;
		        $r = ($rgba >> 16) & 0xff;
		        $g = ($rgba >> 8) & 0xff;
		        $b = $rgba & 0xff;
		        $bytes .= chr($r) . chr($g) . chr($b) . chr($a);
		    }
		}
		@imagedestroy($img);
		$nbt->setTag(new CompoundTag("Skin", [
			new StringTag("Name", "test"),
			new ByteArrayTag("Data", $bytes),
			new ByteArrayTag("CapeData", ""),
			new StringTag("GeometryName", "geometry.target"),
			new ByteArrayTag("GeometryData", $geometryData)
		]));
		parent::__construct($level, $nbt);
	}

	public function attack(EntityDamageEvent $source) : void
	{
		$source->call();
		$this->setLastDamageCause($source);

		if($source->getCause() === EntityDamageEvent::CAUSE_MAGIC) $this->kill();
	}

	public function setHealth()
	{

	}

}

