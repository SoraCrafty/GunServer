<?php
namespace gun\npc;

use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\entity\Skin;
use pocketmine\utils\TextFormat;

class CommandNPC extends NPC{

	const TYPE = 2;

	/*NPCをタッチすると実行するコマンド*/
	private $command;

	public function __construct($name, $size, Skin $skin, Item $item_right, Item $item_left, $helmet, $chestplate, $leggings, $boots, $doGaze, $plugin, $x, $y, $z, $yaw, $pitch, Level $level, $command = "")
	{
		$this->command = $command;

		parent::__construct($name, $size, $skin, $item_right, $item_left, $helmet, $chestplate, $leggings, $boots, $doGaze, $plugin, $x, $y, $z, $yaw, $pitch, $level);
	}

	public function onTouch(Player $player)
	{
		if($this->command !== "") $this->plugin->getServer()->dispatchCommand($player, $this->command);
		else $player->sendMessage(TextFormat::RED . "コマンドが設定されていません");

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
