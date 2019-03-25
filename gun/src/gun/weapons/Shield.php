<?php

namespace gun\weapons;

use pocketmine\math\Vector3;
use pocketmine\entity\Entity;

use gun\weapons\entity\shield\ShieldEntity;

use gun\Callback;

class Shield extends UniqueWeapon
{
	/*シールドの武器カテゴリ*/
	const CATEGORY = self::CATEGORY_SUB;
	/*シールドのID*/
	const WEAPON_ID = "shield";
	/*武器種の名称*/
	const WEAPON_NAME = "Shield";
	/*Loreに書く数値*/
	const ITEM_LORE = [
					"Shield_Use" => [
								"CoolTime" => "クールタイム"
							],
					"Move" =>[
								"Move_Speed" => "移動速度"
							]
					];
	/*デフォルト武器のデータ*/
	const DEFAULT_DATA = [
							"Item_Information" => [
										"Item_Name" => "シールド",
										"Item_Id" => 351,
										"Item_Damage" => 16,
										"Item_Lore" => "地面に設置し、敵の攻撃を防ぐことができる"
										],
							"Shield_Use" => [
										"CoolTime" => 120
									],
							"Shield" => [
										"Health" => 100,
										"Scale" => 1.3,
										"Time_Damage_Interval" => 100,
										"Time_Damage_Amount" => 100
									],
							"Move" =>[
										"Move_Speed" => 0.8
									]
						];

	private $coolTime = [];

	public function onInteract($player, $data)
	{
		$this->onUse($player, $data);
	}

	public function onUseItemOnEntity($player, $data, $args)
	{
		parent::onUseItemOnEntity($player, $data, $args);
		if(!$this->plugin->playerManager->isPC($player)) $this->onUse($player, $data);
	}

	public function onUse($player, $data)
	{
		$name = $player->getName();

		if(!isset($this->coolTime[$name])) $this->coolTime[$name] = false;

		if($this->coolTime[$name] === true) return;

		$baseVector = $player->getDirectionVector()->divide(1.5);
        $nbt = Entity::createBaseNBT(
            $player->asVector3(),
            new Vector3($baseVector->x, 0.2, $baseVector->z),
            $player->yaw,
            0
        );
        $entity = new ShieldEntity($player->getLevel(), $nbt);
        $entity->setDamageInterval($this->data["Shield"]["Time_Damage_Interval"]);
        $entity->setTimeDamage($this->data["Shield"]["Time_Damage_Amount"]);
        $entity->setScale($this->data["Shield"]["Scale"]);
        $entity->setMaxHealth($this->data["Shield"]["Health"]);
        $entity->setHealth($this->data["Shield"]["Health"]);
        $entity->spawnToAll();

        $player->getInventory()->removeItem($player->getInventory()->getItemInHand());
        $this->coolTime[$name] = true;
    	$this->plugin->getScheduler()->scheduleDelayedTask(new CallBack([$this, "giveTask"], [$player]), $this->data["Shield_Use"]["CoolTime"]);
	}

	public function giveTask($player)
	{
		$this->coolTime[$player->getName()] = false;

		if($player->isOnline() && WeaponManager::hasPermission($player))
		{
			$player->getInventory()->addItem($this->get());
		}
	}

}