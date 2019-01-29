<?php

declare(strict_types=1);

namespace gun\fishing\event;

use pocketmine\event\player\PlayerEvent;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\item\Item;
use pocketmine\Player;

class PlayerUseFishRodEvent extends PlayerEvent{

	public function __construct(Player $fisher){
		$this->player = $fisher;
	}
}