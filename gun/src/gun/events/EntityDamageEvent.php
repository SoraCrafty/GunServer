<?php
namespace gun\events;

use pocketmine\Player;
use pocketmine\entity\Human;
use pocketmine\math\Vector3;
use pocketmine\entity\Entity;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\item\Item;
use pocketmine\event\entity\EntityDamageEvent as EntityDamageEventRaw;
use pocketmine\event\entity\EntityDamageByEntityEvent;

use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\network\mcpe\protocol\RemoveEntityPacket;

use gun\Callback;
use gun\game\GameManager;
use gun\provider\AccountProvider;

class EntityDamageEvent extends Events {
	

	/*
	*@priority HIGH
	*/
	public function call($event){
        if($event instanceof EntityDamageByEntityEvent && !$event->isCancelled())
        {
            if(!GameManager::getObject()->isGaming() && $event->getEntity() instanceof Human)
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
		elseif($event->getCause() === EntityDamageEventRaw::CAUSE_VOID && $event->getEntity() instanceof Player)//テスト用のため分割して実装
		{
			$event->getEntity()->teleport($event->getEntity()->getSpawn());
			$event->setCancelled(true);
		}

		/*雑*/
		$player = $event->getEntity();
		if(!$event->isCancelled() && $player instanceof Player)
		{
            if(!$this->plugin->playerManager->isPC($player) &&
               AccountProvider::get()->getSetting($player, "auto_heal") === true &&
               $player->getMaxHealth() - ($player->getHealth() - $event->getBaseDamage()) >= 12 &&
               $player->getInventory()->contains(Item::get(322, 0, 1)) &&
               !$player->hasEffect(Effect::REGENERATION)
            )
            {
				$player->getInventory()->removeItem(Item::get(322, 0, 1));
				$player->addEffect(new EffectInstance(Effect::getEffect(10), 20 * 3, 3, false));
            }
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
