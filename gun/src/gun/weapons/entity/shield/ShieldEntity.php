<?php

namespace gun\weapons\entity\shield;

use pocketmine\level\Level;
use pocketmine\entity\Entity;
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
use pocketmine\network\mcpe\protocol\EntityEventPacket;
use pocketmine\block\Block;

use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\level\sound\AnvilFallSound;
use pocketmine\level\sound\AnvilUseSound;

class ShieldEntity extends Human
{

	protected $maxDeadTicks = 0;

	public $width = 1.5;
	public $height = 1.5;
	public $eyeHeight = 1;

	private $damageInterval = 0;
	private $damageInterval_count = 0;

	private $timeDamage = 20;

	private static $geometryCache = null;
	private static $skinCache = null;

	public function __construct(Level $level, CompoundTag $nbt)
	{
		if(is_null(self::$geometryCache)) self::$geometryCache = file_get_contents(__DIR__ . "/geometry.json");

		if(is_null(self::$skinCache))
		{
			$path = __DIR__ . "/shield.png";
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
			new StringTag("Name", "Shield"),
			new ByteArrayTag("Data", self::$skinCache),
			new ByteArrayTag("CapeData", ""),
			new StringTag("GeometryName", "geometry.shield"),
			new ByteArrayTag("GeometryData", self::$geometryCache)
		]));
		parent::__construct($level, $nbt);
		$this->setNameTag($this->getHealth() . ' / ' . $this->getMaxHealth());
		$this->setNameTagAlwaysVisible(false);
	}	

	public function setDamageInterval($interval)
	{
		$this->damageInterval = $interval;
	}

	public function setTimeDamage($amount)
	{
		$this->timeDamage = $amount;
	}

	public function fall(float $fallDistance) : void
	{
		$this->level->addSound(new AnvilFallSound($this->asVector3()));
		$this->level->addParticle(new DestroyBlockParticle($this, $this->level->getBlock($this->add(0, -0.1, 0))));
	}

	public function broadcastEntityEvent(int $eventId, ?int $eventData = null, ?array $players = null) : void
	{
		if($eventId === EntityEventPacket::HURT_ANIMATION)
		{
			$this->level->addParticle(new DestroyBlockParticle($this->asVector3(), Block::get(42)));
			$this->level->addSound(new AnvilFallSound($this->asVector3()));
		} 
		else parent::broadcastEntityEvent($eventId, $eventData, $players);
	}

	public function knockBack(Entity $attacker, float $damage, float $x, float $z, float $base = 0.4) : void
	{

	}

	public function setHealth(float $amount) : void
	{
		parent::setHealth($amount);
		$this->setNameTag($this->getHealth() . ' / ' . $this->getMaxHealth());
	}

	public function setMaxHealth(int $amount) : void
	{
		parent::setMaxHealth($amount);
		$this->setNameTag($this->getHealth() . ' / ' . $this->getMaxHealth());
	}

	public function entityBaseTick(int $tickDiff = 1) : bool
	{	
		$this->damageInterval_count += $tickDiff;
		if($this->damageInterval_count >= $this->damageInterval)
		{
			$this->setHealth($this->getHealth() - $this->timeDamage);
			$this->damageInterval_count = 0;
		}
		return parent::entityBaseTick($tickDiff);
	}

	protected function onDeath() : void
	{
		$this->despawnFromAll();
		$this->level->addSound(new AnvilFallSound($this->asVector3()));
		for ($i=0; $i < 3; $i++) {
			$position = $this->asVector3()->add(0, $this->eyeHeight, 0)->add(mt_rand(-10, 10) * 0.05  * $this->getScale(), mt_rand(0, 10) * 0.1 * $this->getScale(), mt_rand(-10, 10) * 0.05 * $this->getScale());
			$this->level->addParticle(new DestroyBlockParticle($position, Block::get(42)));
			$this->level->addParticle(new DestroyBlockParticle($position, Block::get(20)));
		}
	}

	protected function onDeathUpdate(int $tickDiff) : bool
	{
		return true;
	}


}