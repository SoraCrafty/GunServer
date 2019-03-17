<?php
namespace gun\scoreboard;

use pocketmine\Player;
use pocketmine\network\mcpe\protocol\ { SetScorePacket, RemoveObjectivePacket, SetDisplayObjectivePacket };
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;

use gun\Provider\AccountProvider;

class ScoreboardManager {

	const OBJECT_NAME = 'gunserver';
	const DISPLAY_NAME = 'BattleFront§c2§f';

	const LINE_EXP = 0;
	const LINE_POINT = 1;
	const LINE_KILL = 2;
	const LINE_DEATH = 3;
	const LINE_KILLRATIO = 4;

	public static function init($plugin)
	{
		$plugin->getServer()->getPluginManager()->registerEvents(new ScoreboardListener($plugin), $plugin);
	}

	public static function prepare(Player $player)
	{
		$pk = new SetDisplayObjectivePacket();
		$pk->displaySlot = 'sidebar';
		$pk->objectiveName = self::OBJECT_NAME;
		$pk->displayName = self::DISPLAY_NAME;
		$pk->criteriaName = "dummy";
		$pk->sortOrder = 0;

		$player->dataPacket($pk);
	}

	public static function setLine(Player $player, int $line, string $message)
	{
		$entry = new ScorePacketEntry();
		$entry->objectiveName = self::OBJECT_NAME;
		$entry->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
		$entry->customName = str_pad("・" . $message, ((strlen(self::DISPLAY_NAME) * 2) - strlen($message)));;
		$entry->score = $line;
		$entry->scoreboardId = $line;
		
		$pk = new SetScorePacket();
		$pk->type = SetScorePacket::TYPE_CHANGE;
		$pk->entries[] = $entry;
		$player->dataPacket($pk);
	}

	public static function removeLine(Player $player, int $line){
		$entry = new ScorePacketEntry();
		$entry->objectiveName = self::OBJECT_NAME;
		$entry->score = $line;
		$entry->scoreboardId = $line;
		
		$pk = new SetScorePacket();
		$pk->type = SetScorePacket::TYPE_REMOVE;
		$pk->entries[] = $entry;
		
		$player->dataPacket($pk);
	}

	public static function updateLine(Player $player, int $line, string $message)
	{
		self::removeLine($player, $line);
		self::setLine($player, $line, $message);
	}

	public static function hide(Player $player)
	{
		$pk = new RemoveObjectPacket();
		$pk->objectiveName = self::OBJECT_NAME;
		
		$player->dataPacket($pk);
	}

}
		
