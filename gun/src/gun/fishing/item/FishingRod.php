<?php

namespace gun\fishing\item;

use pocketmine\item\Tool;
use pocketmine\Player;
use pocketmine\math\Vector3;

use gun\fishing\event\PlayerUseFishRodEvent;

class FishingRod extends Tool{

	public function __construct(){
		parent::__construct(self::FISHING_ROD, 0, "Fishing Rod");
	}

	public function getEnchantability() : int{
		return 1;
	}

	public function getMaxStackSize() : int{
		return 1;
	}

	public function getMaxDurability() : int{
		return 65;
	}

	public function onClickAir(Player $player, Vector3 $directionVector) : bool{
		$event = new PlayerUseFishRodEvent($player);
		$event->call();
		return true;
	}
}