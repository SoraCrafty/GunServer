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
use pocketmine\network\mcpe\protocol\SpawnParticleEffectPacket; 

use gun\Callback;
use gun\Blocks;
use gun\provider\AccountProvider;

class PhotonSword extends UniqueWeapon
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
					"Move" =>[
								"Move_Speed" => "移動速度"
							]
					];
	/*デフォルト武器のデータ*/
	const DEFAULT_DATA = [
							"Item_Information" => [
										"Item_Name" => "§1カゲミツ",
										"Item_Id" => 268,
										"Item_Damage" => 0,
										"Item_Lore" => "エネルギーの刃で銃弾を切り払うことが可能"
										],
							"Attack" => [
										"Damage" => 10,
										"KnockBack" => 1
										],
							"Move" =>[
										"Move_Speed" => 1.4
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
				$entity->broadcastEntityEvent(EntityEventPacket::HURT_ANIMATION, null);
				$entity->setHealth($entity->getHealth() - $data["Attack"]["Damage"]);
				if($data["Attack"]["KnockBack"] > 0) $entity->knockBack($player, $data["Attack"]["Damage"], $entity->x - $player->x, $entity->z - $player->z, 0.4 * $data["Attack"]["KnockBack"]);

				$pk = new LevelSoundEventPacket();
				$pk->sound = LevelSoundEventPacket::SOUND_CAMERA_TAKE_PICTURE;
				$pk->position = $player->asVector3();
				foreach ($player->getLevel()->getPlayers() as $target) {
					$target->dataPacket($pk);
				}
				
				$pk = new SpawnParticleEffectPacket();
				$pk->position = $player->asVector3()->add(0, $player->getEyeHeight(), 0)->add($player->getDirectionVector());
				$pk->particleName = "bf2:totem_particle";
				foreach ($player->getLevel()->getPlayers() as $target) {
					$target->dataPacket($pk);
				}
			}
		}
	}
}

