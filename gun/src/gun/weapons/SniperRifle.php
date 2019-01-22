<?php

namespace gun\weapons;

use pocketmine\math\Vector3;
use pocketmine\block\Block;

use pocketmine\level\sound\ClickSound;
use pocketmine\level\sound\DoorCrashSound;
use pocketmine\level\sound\EndermanTeleportSound;

use pocketmine\level\particle\DestroyBlockParticle;

use pocketmine\event\entity\EntityDamageByEntityEvent;

use pocketmine\network\mcpe\protocol\EntityEventPacket;

use gun\Callback;
use gun\Blocks;

class SniperRifle extends Weapon
{
	/*スナイパーライフルのID*/
	const WEAPON_ID = "sniperrifle";
	/*武器種の名称*/
	const WEAPON_NAME = "SniperRifle";
	/*Loreに書く数値*/
	const ITEM_LORE = [
					"Shooting" => [
								"Cooltime_Between_Shots" => "発射不可時間",
								"Shooting_Damage" => "火力",
								"Shooting_Range" => "射程",
								"Recoil_Amount" => "反動",
								"Bullet_Spread" => "弾ブレ"
								],
					"Reload" => [
								"Reload_Duration" => "リロード時間"
								],
					"Move" =>[
								"Move_Speed" => "移動速度"
							]
					];

	private $reloading = [];
	private $cooltime = [];

	public function get($type)
	{
		$item = parent::get($type);

		$nbt = $item->getNamedTagEntry(Weapon::TAG_WEAPON);
		if($this->weapons[$type]["Reload"]["Enable"])
		{
			$nbt->setInt(Weapon::TAG_BULLET, $this->weapons[$type]["Reload"]["Reload_Amount"]);	
			$item->setCustomName($item->getCustomName() . "§f ▪ «" . $this->weapons[$type]["Reload"]["Reload_Amount"] . "»");
		}
		$item->setNamedTagEntry($nbt);

		return $item;
	}

	public function onInteract($player, $data)
	{
		$name = $player->getName();

		if(!isset($this->reloading[$name])) $this->reloading[$name] = false;

		if($this->reloading[$name]) return true;

		if($player->isSneaking() && $data["Reload"]["Enable"])
		{
			$this->reloading[$name] = true;
			$this->ReloadTask($player, $data, 0);
			return true;
		}
	}

	public function onShootBow($player, $data, $args)
	{
		$event = $args[0];
		$name = $player->getName();

		if(!isset($this->cooltime[$name])) $this->cooltime[$name] = false;

		if(!$this->cooltime[$name]) $this->Shoot($player, $data);

		$event->setCancelled(true);
	}

	public function Shoot($player, $data)
	{
		$name = $player->getName();

		$weapon = $player->getInventory()->getItemInHand();
		$tag = $weapon->getNamedTagEntry(Weapon::TAG_WEAPON);

		if($data["Reload"]["Enable"])//弾減らしたりする処理
		{
			$bullet = $tag->getTag(Weapon::TAG_BULLET)->getValue();
			if($bullet <= 0)
			{
				$this->shooting[$name] = false;
				$player->getLevel()->addSound(new ClickSound($player->asVector3(), -100), [$player]);
				return true;
			}

			$bullet--;
			$tag->setInt(Weapon::TAG_BULLET, $bullet, true);
			$weapon->setNamedTagEntry($tag);
			$weapon->setCustomName($data["Item_Information"]["Item_Name"] . "§f ▪ «" . $bullet . "»");
			$player->sendPopUp("§o" . $weapon->getCustomName());
			$player->getInventory()->setItemInHand($weapon);
		}

		if($data["Shooting"]["Recoil_Amount"] > 0)//反動つける処理
		{
			if(!($player->isSneaking() && $data["Sneak"]["Enable"] && $data["Sneak"]["No_Recoil"]))
			{
				$motion = $player->getDirectionVector()->multiply(-0.2);
				if(!$player->isOnGround() && !$player->isUnderwater())//地に足がついてない状態では反動が軽減され下に落下する(水中は例外)
				{
					$motion->multiply(0.5);
					$motion->y = -1;
				}
				$player->setMotion($motion);
			}
		}

		/*銃弾の処理*/
		$level = $player->getLevel();
		$pos = $player->asVector3();
		$pos->y += $player->getEyeHeight();
		$speread = ($player->isSneaking() && $data["Sneak"]["Enable"]) ? $data["Sneak"]["Bullet_Spread"] : $data["Shooting"]["Bullet_Spread"];
		$pitch = $player->pitch + mt_rand(-$speread, $speread);
		$yaw = $player->yaw + mt_rand(-$speread, $speread);
		$motionY = -sin(deg2rad($pitch));
		$motionXZ = cos(deg2rad($pitch));
		$motionX = -$motionXZ * sin(deg2rad($yaw));
		$motionZ = $motionXZ * cos(deg2rad($yaw));
		$motion = new Vector3($motionX, $motionY, $motionZ);
		for ($i = 0; $i < $data["Shooting"]["Shooting_Range"]; $i++) { 
			$pos = $pos->add($motion);
			$block = $level->getBlock($pos);
			if(Blocks::isSolid($block->getId()))
			{
				$level->addParticle(new DestroyBlockParticle($pos, $block));
				break;
			}

			foreach ($level->getEntities() as $entity) {
				if($entity->getId() != $player->getId()){//エラー吐くので
					if($pos->distance($entity->asVector3()->add(0, $entity->height / 2, 0)) <= sqrt($entity->height ** 2 + $entity->width ** 2) / 2)
					{
						$damage = $data["Shooting"]["Shooting_Damage"];
						if($pos->distance($entity->asVector3()->add(0, $entity->getEyeHeight(), 0)) <= 0.7)
						{
							$damage *= 1.5;
							$level->addParticle(new DestroyBlockParticle($pos, Block::get(216, 0)));
							$player->addTitle('§4>   <', '', 1, 1, 1);
						}
						else{
							$player->addTitle('>   <', '', 1, 1, 1);
						}
						$event = new EntityDamageByEntityEvent($player, $entity, EntityDamageByEntityEvent::CAUSE_ENTITY_ATTACK, $damage, [], 0);
						$event->call();
						if(!$event->isCancelled())
						{
    						$entity->setLastDamageCause($event);
    						$entity->broadcastEntityEvent(EntityEventPacket::HURT_ANIMATION, null, $level->getPlayers());
    						$entity->setHealth($entity->getHealth() - 1);
    						$level->addParticle(new DestroyBlockParticle($pos, Block::get(236,14)));
						}
						break 2;
					}
				}
			}
		}

		$level->addSound(new DoorCrashSound($player->asVector3(), -100));

		$this->cooltime[$player->getName()] = true;
		$this->CooltimeTask($player, $data, 0);
	}

	public function CooltimeTask($player, $data, $phase){//クールタイム関連は要改善
		$name = $player->getName();

		if(!$player->isOnline()){
			$this->cooltime[$name] = false;
			return true;
		}

		$weapon = $player->getInventory()->getItemInHand();
		$tag = $weapon->getNamedTagEntry(Weapon::TAG_WEAPON);

		if(!is_null($tag) && $this->getData($tag->getTag(Weapon::TAG_TYPE)->getValue()) === $data)
		{
			$progress = round($phase / $data["Shooting"]["Cooltime_Between_Shots"] * 30);
			if($phase >= $data["Shooting"]["Cooltime_Between_Shots"])
			{
				$text = "§r§o" . $weapon->getCustomName() . "©";
				$player->getLevel()->addSound(new EndermanTeleportSound($player->asVector3(), 0), [$player]);
			}
			else
			{
				$text = "§lCooltime §b" . str_repeat("‖", 30 - $progress) . "§f" . str_repeat("‖",$progress) . "\n§r§o" . $weapon->getCustomName() . "©";
			}
			$player->sendPopUp($text);
		}

		if($phase >= $data["Shooting"]["Cooltime_Between_Shots"]){
			$this->cooltime[$name] = false;
			return true;
		}

		$phase ++;

		$this->plugin->getScheduler()->scheduleDelayedTask(new CallBack([$this, "CooltimeTask"], [$player, $data, $phase]), 1);
	}	

	public function ReloadTask($player, $data, $phase){
		$name = $player->getName();

		if(!$player->isOnline()){
			$this->reloading[$name] = false;
			return true;
		}

		$weapon = $player->getInventory()->getItemInHand();
		$tag = $weapon->getNamedTagEntry(Weapon::TAG_WEAPON);
		if(is_null($tag) || $this->getData($tag->getTag(Weapon::TAG_TYPE)->getValue()) !== $data)
		{
			$this->reloading[$name] = false;
			return true;
		}

		if($phase >= $data["Reload"]["Reload_Duration"]){
			$tag->setInt(Weapon::TAG_BULLET, $data["Reload"]["Reload_Amount"], true);
			$weapon->setNamedTagEntry($tag);
			$weapon->setCustomName($data["Item_Information"]["Item_Name"] . "§f ▪ «" . $data["Reload"]["Reload_Amount"] . "»");
			$player->getInventory()->setItemInHand($weapon);
			$player->sendPopUp("§lReloaded §a‖‖‖‖‖‖‖‖‖‖‖‖‖‖‖‖‖‖‖‖‖‖‖‖‖‖‖‖‖‖§f(100％)\n§r§o" . $weapon->getCustomName());
			$player->getLevel()->addSound(new EndermanTeleportSound($player->asVector3(), 0), [$player]);
			$this->reloading[$player->getName()] = false;
			return true;
		}

		$phase ++;
		$progress = round($phase / $data["Reload"]["Reload_Duration"] * 30);
		$text = "§lReloading §a" . str_repeat("‖", $progress) . "§f" . str_repeat("‖",30 - $progress) . "(" . round($progress * 3.3) . "％)\n§r§o" . $weapon->getCustomName() . "®";
		$player->sendPopUp($text);
		if($phase % 2 === 0) $player->getLevel()->addSound(new ClickSound($player->asVector3(), -100), [$player]);
		$this->plugin->getScheduler()->scheduleDelayedTask(new CallBack([$this, "ReloadTask"], [$player, $data, $phase]), 1);
	}
}

