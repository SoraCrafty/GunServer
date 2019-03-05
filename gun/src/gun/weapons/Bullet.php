<?php

namespace gun\weapons;

use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\entity\projectile\Projectile;
use pocketmine\math\RayTraceResult;
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\network\mcpe\protocol\EntityEventPacket;
use pocketmine\nbt\tag\CompoundTag;

class Bullet extends Projectile
{
	public const NETWORK_ID = self::SNOWBALL;

	public $width = 0.3;
	public $height = 0.3;

	protected $gravity = 0.005;

	private $progress = 0;

	protected function onHitBlock(Block $blockHit, RayTraceResult $hitResult) : void{
		parent::onHitBlock($blockHit, $hitResult);
		$this->level->addParticle(new DestroyBlockParticle($this, $blockHit));
		$this->kill();
	}

	protected function onHitEntity(Entity $entityHit, RayTraceResult $hitResult) : void{
		$shooter = $this->getOwningEntity();
		$damage = $entityHit->add(0, $entityHit->getEyeHeight(), 0)->distance($this) < 0.5 ? $this->damage * 2 : $this->damage;
		if(is_null($shooter)) $event = new EntityDamageByEntityEvent($this, $entityHit, EntityDamageByEntityEvent::CAUSE_ENTITY_ATTACK, $damage, [], 0);
		else $event = new EntityDamageByEntityEvent($this->getOwningEntity(), $entityHit, EntityDamageByEntityEvent::CAUSE_ENTITY_ATTACK, $damage, [], 0);
		$event->call();
		if(!$event->isCancelled())
		{
			$entityHit->setLastDamageCause($event);
			$entityHit->broadcastEntityEvent(EntityEventPacket::HURT_ANIMATION, null);
			$entityHit->setHealth($entityHit->getHealth() - $damage);
		}
		$this->kill();
	}

	public function onUpdate(int $currentTick) : bool
	{
		$result = parent::onUpdate($currentTick);
		if($result === false) return false;

		$this->progress++;
		if($this->progress > 200)
		{
			$this->kill();
		}

		return true;
	}

}

