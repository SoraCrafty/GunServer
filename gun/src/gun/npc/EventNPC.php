<?php
namespace gun\npc;

use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\entity\Skin;
use pocketmine\utils\TextFormat;

class EventNPC extends NPC{

	const TYPE = 3;

	/*NPCのイベントID*/
	private $event;

	public function __construct($name, $size, Skin $skin, Item $item_right, Item $item_left, $helmet, $chestplate, $leggings, $boots, $doGaze, $plugin, $x, $y, $z, $yaw, $pitch, Level $level, $event = "")
	{
		$this->event = $event;

		parent::__construct($name, $size, $skin, $item_right, $item_left, $helmet, $chestplate, $leggings, $boots, $doGaze, $plugin, $x, $y, $z, $yaw, $pitch, $level);
	}

	public static function fromSimpleData($plugin, $data)
	{
		$npc = parent::fromSimpleData($plugin, $data);
		$npc->setEvent($data["event"]);
		return $npc;
	}

	public function onTouch(Player $player)
	{
		$event = new EventNPCTouchEvent($player, $this->event);
		$event->call();

		parent::onTouch($player);
	}

	public function setEvent($event)
	{
		$this->event = $event;
	}

	public function getEvent()
	{
		return $this->event;
	}

	public function getSimpleData()
	{
		$data = parent::getSimpleData();
		$data["event"] = $this->event;
		return $data;
	}
}
