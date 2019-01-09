<?php
namespace gun\npc;

use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\entity\Skin;

class MessageNPC extends NPC{

	const NPC_TYPE = 1;

	/*NPCが返答するメッセージの配列*/
	private $messages = [];

	public function __construct($name, $size, Skin $skin, Item $item_right, Item $item_left, $helmet, $chestplate, $leggings, $boots, $doGaze, $x, $y, $z, $yaw, $pitch, Level $level, $messages)
	{
		$this->messages = $messages;

		parent::__construct($name, $size, $skin, $item_right, $item_left, $helmet, $chestplate, $leggings, $boots, $doGaze, $x, $y, $z, $yaw, $pitch, $level);
	}

	public function onTouch(Player $player)
	{
		$player->sendMessage($this->messages[array_rand($this->messages)]);

		parent::onTouch($player);
	}

	public function addMessage($message)
	{
		$this->messages[] = $message;
	}

	public function setMessages($messages)
	{
		$this->messages = $messages;
	}

	public function getMessages()
	{
		return $this->messages;
	}
}
