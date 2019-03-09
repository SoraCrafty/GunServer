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

class Bullet extends Projectile
{
	public const NETWORK_ID = self::SNOWBALL;

	public $width = 0.3;
	public $height = 0.3;

	protected $gravity = 0.005;

	private $MotionProgress = 0;
	private $progress = 0;

	protected function onHitBlock(Block $blockHit, RayTraceResult $hitResult) : void{
		parent::onHitBlock($blockHit, $hitResult);
		$this->level->addParticle(new DestroyBlockParticle($this->asVector3(), $blockHit));
		$this->flagForDespawn();
	}

	protected function onHitEntity(Entity $entityHit, RayTraceResult $hitResult) : void{
		$shooter = $this->getOwningEntity();
		$damage = /*$entityHit->asVector3()->add(0, $entityHit->getEyeHeight(), 0)->distance($this->asVector3()) < 0.5 ? $this->damage * 2 : */$this->damage;
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

	public function onUpdate(int $currentTick) : bool
	{
		$result = parent::onUpdate($currentTick);
		if($result === false) return false;

		$this->MotionProgress += $this->motion->asVector3()->distance(new Vector3(0, 0, 0));
		if($this->MotionProgress > 80)
		{
			$this->level->addParticle(new SnowballPoofParticle($this->asVector3()));
			$this->flagForDespawn();
			return true;
		}

		$this->progress++;
		if($this->progress > 20)
		{
			$this->level->addParticle(new SnowballPoofParticle($this->asVector3()));
			$this->flagForDespawn();
			return true;
		}

		return true;
	}

}

