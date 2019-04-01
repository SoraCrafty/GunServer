<?php

namespace gun\weapons;

use pocketmine\math\Vector3;
use pocketmine\block\Block;
use pocketmine\entity\Entity;

use pocketmine\level\sound\ClickSound;
use pocketmine\level\sound\DoorCrashSound;
use pocketmine\level\sound\EndermanTeleportSound;

use pocketmine\level\particle\DestroyBlockParticle;

use pocketmine\event\entity\EntityDamageByEntityEvent;

use pocketmine\network\mcpe\protocol\EntityEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;  

use gun\Callback;
use gun\Blocks;

class SniperRifle extends Weapon
{
	/*スナイパーライフルの武器カテゴリ*/
	const CATEGORY = self::CATEGORY_MAIN;
	/*スナイパーライフルのID*/
	const WEAPON_ID = "sniperrifle";
	/*武器種の名称*/
	const WEAPON_NAME = "SniperRifle";
	/*Loreに書く数値*/
	const ITEM_LORE = [
					"Shooting" => [
								"Cooltime_Between_Shots" => "クールタイム",
								"Shooting_Damage" => "火力",
								"Bullet_Speed" => "弾速",
								"Recoil_Amount" => "反動",
								"Bullet_Spread" => "弾ブレ"
								],
					"Reload" => [
								"Reload_Amount" => "最大装填数",
								"Reload_Duration" => "リロード時間"
								],
					"Move" =>[
								"Move_Speed" => "移動速度"
							]
					];
	/*デフォルト武器のデータ*/
	const DEFAULT_DATA = [
							"Kar98k" => [
								"Item_Information" => [
											"Item_Name" => "§cKar98k",
											"Item_Id" => 280,
											"Item_Damage" => 0,
											"Item_Lore" => "ドイツで開発されたボルトアクション式小銃"
											],
								"Shooting" => [
											"Cooltime_Between_Shots" => 20,
											"Shooting_Damage" => 20,
											"Bullet_Speed" => 5,
											"Recoil_Amount" => 1.5,
											"Bullet_Spread" => 1
											],
								"Sneak" => [
											"Enable" => true,
											"No_Recoil" => true,
											"Bullet_Spread" => 0
											],
								"Reload" => [
											"Enable" => true,
											"Reload_Amount" => 9,
											"Reload_Duration" => 100
											],
								"Move" =>[
											"Move_Speed" => 0.7
										]
									   ]
						];

	private $reloading = [];
	private $cooltime = [];

	public function get($type)
	{
		if(!isset($this->weapons[$type])) return null;

		$item = parent::get($type);

		$nbt = $item->getNamedTagEntry(Weapon::TAG_WEAPON);
		if($this->weapons[$type]["Reload"]["Enable"])
		{
			$nbt->setInt(Weapon::TAG_BULLET, $this->weapons[$type]["Reload"]["Reload_Amount"]);	
			$item->setCustomName($item->getCustomName() . "§f ▪ «" . $this->weapons[$type]["Reload"]["Reload_Amount"] . "»");
		}
		else
		{
			$item->setCustomName($item->getCustomName() . "§f ▪ «∞»");
		}
		$item->setNamedTagEntry($nbt);

		return $item;
	}

	public function onInteract($player, $data)
	{
		$this->onPreInteract($player, $data);
	}

	public function onUseItemOnEntity($player, $data, $args)
	{
		parent::onUseItemOnEntity($player, $data, $args);
		if(!$this->plugin->playerManager->isPC($player)) $this->onInteract($player, $data);
	}

	public function onPreInteract($player, $data)
	{
		$name = $player->getName();

		if(!isset($this->reloading[$name])) $this->reloading[$name] = false;

		if($player->isSneaking() && $data["Reload"]["Enable"])
		{
			$this->reloading[$name] = true;
			$this->ReloadTask($player, $data, 0);
			return true;
		}

		if($this->reloading[$name]) return true;

		if(!isset($this->cooltime[$name])) $this->cooltime[$name] = false;

		if(!$this->cooltime[$name]) $this->Shoot($player, $data);
	}

	public function onDropItem($player, $data, $args)
	{
		$event = $args[0];
		$event->setCancelled(true);

		$name = $player->getName();//同じ処理だし関数にしたほうがいいかも

		if(!isset($this->reloading[$name])) $this->reloading[$name] = false;

		if($this->reloading[$name]) return true;

		if($data["Reload"]["Enable"])
		{
			$this->reloading[$name] = true;
			$this->ReloadTask($player, $data, 0);
			return true;
		}
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
				$this->reloading[$name] = true;
				$this->ReloadTask($player, $data, 0);
				return true;
			}

			$bullet--;
			$tag->setInt(Weapon::TAG_BULLET, $bullet, true);
			$weapon->setNamedTagEntry($tag);
			$weapon->setCustomName($data["Item_Information"]["Item_Name"] . "§f ▪ «" . $bullet . "»");
			$player->getInventory()->setItemInHand($weapon);
			//$this->plugin->getScheduler()->scheduleDelayedTask(new CallBack([$this, "giveTask"], [$player, $weapon]), 1);//minecraftの仕様対策
		}

		$player->sendPopUp("§o" . $weapon->getCustomName());

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

		$speread = ($player->isSneaking() && $data["Sneak"]["Enable"]) ? $data["Sneak"]["Bullet_Spread"] : $data["Shooting"]["Bullet_Spread"];
		$pitch = $player->pitch + mt_rand(-$speread * 10, $speread * 10) * 0.1;
		$yaw = $player->yaw + mt_rand(-$speread * 10, $speread * 10) * 0.1;
		$motionY = -sin(deg2rad($pitch));
		$motionXZ = cos(deg2rad($pitch));
		$motionX = -$motionXZ * sin(deg2rad($yaw));
		$motionZ = $motionXZ * cos(deg2rad($yaw));
		$motion = new Vector3($motionX, $motionY, $motionZ);

		$nbt = Entity::createBaseNBT(
			$player->add(0, $player->getEyeHeight(), 0),
			$motion->multiply($data["Shooting"]["Bullet_Speed"]),
			($player->yaw > 180 ? 360 : 0) - $player->yaw,
			-$player->pitch
		);

		$entity = new Bullet($level, $nbt, $player);//Entity::create(Bullet::class, $player->getLevel(), $nbt, $player);
		$entity->setBaseDamage($data["Shooting"]["Shooting_Damage"]);
		$entity->spawnToAll();

		/*音の処理*/
		$pk = new PlaySoundPacket();
		$pk->soundName = "bf2.sniperrifle_shot";
		$pk->x = $player->x;
		$pk->y = $player->y;
		$pk->z = $player->z;
		$pk->volume = 2;
		$pk->pitch = 1;
		foreach ($player->getLevel()->getPlayers() as $target) {
			$target->dataPacket($pk);
		}

		$this->cooltime[$player->getName()] = true;
		$this->CooltimeTask($player, $data, 0);
	}

	/*public function giveTask($player, $weapon)//minecraftの仕様対策
	{
		if($player->isOnline())
		{
			$uniqueId = $weapon->getNamedTagEntry(self::TAG_WEAPON)->getTag(Weapon::TAG_UNIQUE_ID)->getValue();
			foreach ($player->getInventory()->getContents() as $slot => $item) {
				$tag = $item->getNamedTagEntry(self::TAG_WEAPON);
				if(!is_null($tag) && $tag->getTag(Weapon::TAG_UNIQUE_ID)->getValue() === $uniqueId)
				{
					$player->getInventory()->setItem($slot, $weapon);
					break;
				}
			}
		}
 	}*/

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
				$text = "§r§o" . $weapon->getCustomName();
				$player->getLevel()->addSound(new EndermanTeleportSound($player->asVector3(), 0), [$player]);
			}
			else
			{
				$text = "§lCooltime §b" . str_repeat("‖", 30 - $progress) . "§f" . str_repeat("‖",$progress) . "\n§r§o" . $weapon->getCustomName() . "©";
			}

			if(!$this->reloading[$name]) $player->sendPopUp($text);
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

