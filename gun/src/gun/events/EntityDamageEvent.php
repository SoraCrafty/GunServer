<?php
namespace gun\events;

use pocketmine\Player;
use pocketmine\math\Vector3;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageEvent as EntityDamageEventRaw;
use pocketmine\event\entity\EntityDamageByEntityEvent;

use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\network\mcpe\protocol\RemoveEntityPacket;

use gun\Callback;
use gun\game\GameManager;

class EntityDamageEvent extends Events {
	

	/*
	*@priority HIGH
	*/
	public function call($event){
        if($event instanceof EntityDamageByEntityEvent && !$event->isCancelled())
        {
            if(!GameManager::getObject()->isGaming() && $event->getEntity() instanceof Player)
            {
                $event->setCancelled(true);
            }
            else
            {
            	$attacker = $event->getDamager();
            	if($attacker instanceof Player && $attacker->isOnline()) $this->hitParticle($attacker, $event->getEntity(), $event->getBaseDamage());
            }
        }
		elseif($event->getCause() === EntityDamageEventRaw::CAUSE_FALL)//テスト用のため分割して実装
		{
			$damage = round($event->getBaseDamage() / 5);
			if($damage <= 1) $event->setCancelled(true);
			$event->setBaseDamage(round($damage));
		}
	}

	/*別クラスに移動、雑！！*/
	public function hitParticle($attacker, $damager, $damage)
	{
		$pk = new AddEntityPacket();
		$eid = Entity::$entityCount++;
		$pk->entityRuntimeId = $eid;
		$pk->type = Entity::ITEM;
		$pk->position = $damager->asVector3()->add(mt_rand(-8, 8) * 0.1, $damager->getEyeHeight() / 2 + mt_rand(-5, 5) * 0.1, mt_rand(-8, 8) * 0.1);
		$pk->motion = new Vector3(0, 0.15, 0);
        $flags = 0;
        $flags |= 1 << Entity::DATA_FLAG_CAN_SHOW_NAMETAG;
        $pk->metadata = [
          Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, $flags],
          Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, "§c♥ §f{$damage}"],
          Entity::DATA_ALWAYS_SHOW_NAMETAG => [Entity::DATA_TYPE_BYTE, 1],
          Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 1]
        ];

		$attacker->dataPacket($pk);
		$this->plugin->getScheduler()->scheduleDelayedTask(new Callback([$this, 'removeHitParticle'], [$attacker, $eid]), 8);
	}

	public function removeHitParticle($attacker, $eid)
	{
		$pk = new RemoveEntityPacket();
		$pk->entityUniqueId = $eid;
		$attacker->dataPacket($pk);
	}
	
}
