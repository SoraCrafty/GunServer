<?php
namespace gun\npc;

use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\entity\Skin;

class CommandNPC extends NPC{

	const NPC_TYPE = 2;

	/*Mainクラスのオブジェクト*/
	private $plugin;
	/*NPCをタッチすると実行するコマンド*/
	private $command;

	public function __construct($name, $size, Skin $skin, Item $item_right, Item $item_left, $helmet, $chestplate, $leggings, $boots, $doGaze, $x, $y, $z, $yaw, $pitch, Level $level, $plugin, $command)
	{
		$this->plugin = $plugin;
		$this->command = $command;

		parent::__construct($name, $size, $skin, $item_right, $item_left, $helmet, $chestplate, $leggings, $boots, $doGaze, $x, $y, $z, $yaw, $pitch, $level);
	}

	public function onTouch(Player $player)
	{
		$this->plugin->getServer()->dispatchCommand($player, $this->command);

		parent::onTouch($player);
	}

	public function setCommand($command)
	{
		$this->command = $command;
	}

	public function getCommand()
	{
		return $this->command;
	}
}
