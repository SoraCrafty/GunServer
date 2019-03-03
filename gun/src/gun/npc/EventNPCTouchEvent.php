<?php

namespace gun\npc;

use pocketmine\Player;

use pocketmine\event\player\PlayerEvent;

class EventNPCTouchEvent extends PlayerEvent
{

	private $eventId = "";

	public function __construct(Player $player, string $eventId)
	{
		$this->player = $player;
		$this->eventId = $eventId;
	}

	public function getEventId()
	{
		return $this->eventId;
	}

}