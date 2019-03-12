<?php

namespace gun\weapons;

use pocketmine\math\Vector3;
use pocketmine\math\AxisAlignedBB;
use pocketmine\block\Block;
use pocketmine\entity\Entity;

use pocketmine\level\sound\ClickSound;
use pocketmine\level\sound\DoorCrashSound;
use pocketmine\level\sound\EndermanTeleportSound;

use pocketmine\level\particle\SnowballPoofParticle;
use pocketmine\level\particle\LavaParticle;
use pocketmine\level\particle\SmokeParticle;
use pocketmine\level\particle\CriticalParticle;

use pocketmine\event\entity\EntityDamageByEntityEvent;

use pocketmine\network\mcpe\protocol\EntityEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket; 

use gun\Callback;
use gun\Blocks;
use gun\provider\AccountProvider;

class PhotonSword extends Weapon
{
	/*フォトンソードの武器カテゴリ*/
	const CATEGORY = self::CATEGORY_MAIN;
	/*フォトンソードのID*/
	const WEAPON_ID = "photonsword";
	/*武器種の名称*/
	const WEAPON_NAME = "PhotonSword";
	/*Loreに書く数値*/
	const ITEM_LORE = [
					"Attack" => [
								"Damage" => "ダメージ",
								"KnockBack" => "ノックバック",
								],
					"Reject_Bullet" => [
								"Duration" => "持続時間",
								"Range" => "斬撃範囲",
								"CoolTime" => "クールタイム"
								],
					"Move" =>[
								"Move_Speed" => "移動速度"
							]
					];
	/*デフォルト武器のデータ*/
	const DEFAULT_DATA = [
							"KagemitsuG1" => [
								"Item_Information" => [
											"Item_Name" => "§0カゲミツG1",
											"Item_Id" => 268,
											"Item_Damage" => 0,
											"Item_Lore" => "エネルギーの刃で銃弾を切り払うことが可能"
											],
								"Attack" => [
											"Damage" => 5,
											"KnockBack" => 1
											],
								"Reject_Bullet" =>[
											"Enable" => true,
											"Duration" => 60,
											"Range" => 5,
											"CoolTime" => 20
											],
								"Move" =>[
											"Move_Speed" => 1.2
										]
									   ]
						];

	private $cooltime = [];

	public function onUseItemOnEntity($player, $data, $args)
	{
		parent::onUseItemOnEntity($player, $data, $args);
		$entity = $player->getLevel()->getEntity($args[0]->getPacket()->trData->entityRuntimeId);
		if(!is_null($entity))
		{
			$event = new EntityDamageByEntityEvent($player, $entity, EntityDamageByEntityEvent::CAUSE_ENTITY_ATTACK, $data["Attack"]["Damage"], [], $data["Attack"]["KnockBack"]);
			$event->call();
			if(!$event->isCancelled())
			{
				$entity->setLastDamageCause($event);
				$entity->broadcastEntityEvent(EntityEventPacket::HURT_ANIMATION, null);
				$entity->setHealth($entity->getHealth() - $data["Attack"]["Damage"]);
				$entity->setMotion($player->getDirectionVector()->multiply($data["Attack"]["KnockBack"]));
			}
		}
	}

	public function onInteract($player, $data)
	{
		$this->onPreInteract($player, $data);
	}

	public function onPreInteract($player, $data)
	{
		$name = $player->getName();

		if(!isset($this->cooltime[$name])) $this->cooltime[$name] = false;

		if(!$this->cooltime[$name])
		{
			$this->cooltime[$name] = true;
			$this->Reject_Bullet_Task($player, $data, 0);
		}
	}


	public function Reject_Bullet_Task($player, $data, $phase)
	{
		$name = $player->getName();

		if(!$player->isOnline()){
			$this->cooltime[$name] = false;
			return true;
		}

		for ($i=0; $i < 5; $i++) { 
			$player->getLevel()->addParticle(new CriticalParticle($player->asVector3()->add(mt_rand(-20, 20)*0.1, mt_rand(-20, 20)*0.1, mt_rand(-20, 20)*0.1)));
		}

		foreach ($player->getLevel()->getEntities() as $entity) {
			if($entity instanceof Bullet && $entity->distance($player) <= $data["Reject_Bullet"]["Range"])
			{
				$entity->kill();
				$player->getLevel()->addParticle(new SnowballPoofParticle($entity->asVector3()));
				$player->getLevel()->addParticle(new LavaParticle($entity->asVector3()));
			}
		}

		$phase++;
		if($phase >= $data["Reject_Bullet"]["Duration"])
		{
			$this->cooltime[$name] = true;
			$this->CooltimeTask($player, $data, 0);
			return true;
		}

		$this->plugin->getScheduler()->scheduleDelayedTask(new CallBack([$this, "Reject_Bullet_Task"], [$player, $data, $phase]), 1);
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
			$progress = round($phase / $data["Reject_Bullet"]["CoolTime"] * 30);
			if($phase >= $data["Reject_Bullet"]["CoolTime"])
			{
				$text = "§r§o" . $weapon->getCustomName();
				$player->getLevel()->addSound(new EndermanTeleportSound($player->asVector3(), 0), [$player]);
			}
			else
			{
				$text = "§lCooltime §0" . str_repeat("‖", 30 - $progress) . "§f" . str_repeat("‖",$progress) . "\n§r§o" . $weapon->getCustomName() . "©";
			}

			/*if(!$this->reloading[$name])*/ $player->sendPopUp($text);
		}

		if($phase >= $data["Reject_Bullet"]["CoolTime"]){
			$this->cooltime[$name] = false;
			return true;
		}

		$phase ++;

		$this->plugin->getScheduler()->scheduleDelayedTask(new CallBack([$this, "CooltimeTask"], [$player, $data, $phase]), 1);
	}
}

