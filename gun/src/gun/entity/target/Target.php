<?php

namespace gun\entity\target;

use pocketmine\level\Level;
use pocketmine\entity\Human;
use pocketmine\entity\Entity;
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

	private static $geometryCache = null;
	private static $skinCache = null;

	public function __construct(Level $level, CompoundTag $nbt)
	{
		if(is_null(self::$geometryCache)) self::$geometryCache = file_get_contents(__DIR__ . "/geometry.json");

		if(is_null(self::$skinCache))
		{
			$path = __DIR__ . "/skin.png";
			$img = @imagecreatefrompng($path);
			self::$skinCache = '';
			$l = (int) @getimagesize($path)[1];
			for ($y = 0; $y < $l; $y++) {
			    for ($x = 0; $x < 64; $x++) {
			        $rgba = @imagecolorat($img, $x, $y);
			        $a = ((~((int)($rgba >> 24))) << 1) & 0xff;
			        $r = ($rgba >> 16) & 0xff;
			        $g = ($rgba >> 8) & 0xff;
			        $b = $rgba & 0xff;
			        self::$skinCache .= chr($r) . chr($g) . chr($b) . chr($a);
			    }
			}
			@imagedestroy($img);
		}

		$nbt->setTag(new CompoundTag("Skin", [
			new StringTag("Name", "Target"),
			new ByteArrayTag("Data", self::$skinCache),
			new ByteArrayTag("CapeData", ""),
			new StringTag("GeometryName", "geometry.target"),
			new ByteArrayTag("GeometryData", self::$geometryCache)
		]));
		parent::__construct($level, $nbt);
	}

	public function attack(EntityDamageEvent $source) : void
	{
		$source->call();
		$this->setLastDamageCause($source);

		if($source->getCause() === EntityDamageEvent::CAUSE_MAGIC) $this->kill();
	}

	public function setHealth(float $amount) : void
	{

	}

	public function knockBack(Entity $attacker, float $damage, float $x, float $z, float $base = 0.4) : void
	{

	}

}

