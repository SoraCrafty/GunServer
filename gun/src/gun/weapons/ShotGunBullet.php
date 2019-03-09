<?php

namespace gun\weapons;

use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\entity\projectile\Projectile;
use pocketmine\math\RayTraceResult;
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\level\particle\SnowballPoofParticle;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\network\mcpe\protocol\EntityEventPacket;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\math\Vector3;
use pocketmine\level\Level;

class ShotGunBullet extends Bullet
{

	private $basePosition;
	private $decayLevel = 0;

	public function __construct(Level $level, CompoundTag $nbt, ?Entity $shootingEntity = null)
	{
		parent::__construct($level, $nbt, $shootingEntity);
		$this->basePosition = $this->asVector3();
	}

	public function setDecayLevel($decayLevel)
	{
		$this->decayLevel = $decayLevel;
	}

	protected function onHitEntity(Entity $entityHit, RayTraceResult $hitResult) : void{
		$shooter = $this->getOwningEntity();
		$distance = $this->basePosition->distance($this->asVector3());
		$damage = $this->decayLevel >= 80 ? 1 : /*$entityHit->asVector3()->add(0, $entityHit->getEyeHeight(), 0)->distance($this->asVector3()) < 0.5 ? $this->damage * 2 : */ round($this->damage - ($distance**2 * $this->damage/((80-$this->decayLevel)**2)));
		if($damage < 1) $damage = 1;
		if(is_null($shooter)) $event = new EntityDamageByEntityEvent($this, $entityHit, EntityDamageByEntityEvent::CAUSE_ENTITY_ATTACK, $damage, [], 0);
		else $event = new EntityDamageByEntityEvent($this->getOwningEntity(), $entityHit, EntityDamageByEntityEvent::CAUSE_ENTITY_ATTACK, $damage, [], 0);
		$event->call();
		if(!$event->isCancelled())
		{
			$entityHit->setLastDamageCause($event);
			$entityHit->broadcastEntityEvent(EntityEventPacket::HURT_ANIMATION, null);
			$entityHit->setHealth($entityHit->getHealth() - $damage);
		}
		$this->flagForDespawn();
	}

}

