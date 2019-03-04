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

class ThrowingKnife extends Weapon
{
	/*投げナイフの武器カテゴリ*/
	const CATEGORY = self::CATEGORY_SUB;
	/*投げナイフのID*/
	const WEAPON_ID = "throwingknife";
	/*武器種の名称*/
	const WEAPON_NAME = "ThrowingKnife";
	/*Loreに書く数値*/
	const ITEM_LORE = [];
	/*デフォルト武器のデータ*/
	const DEFAULT_DATA = [];
}

