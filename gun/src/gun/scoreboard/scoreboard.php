<?php
namespace gun\scoreboard;

use pocketmine\Player;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\Listener;
use pocketmine\network\mcpe\protocol\ { SetScorePacket, RemoveObjectivePacket, SetDisplayObjectivePacket };
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;

class scoreboard implements Listener{
	
	//表示する場所
	const displaySlot = 'sidebar';
	//分からんけど多分id的なものだと思う
	const objectiveName = 'gunserver';
	//スコアボードのタイトル
	const displayName = 'BattleFront';
	//並べ方 0が昇順 1が降順
	const sortOrder = 0;
	
	public function __construct($plugin){
		$this->plugin = $plugin;
		$this->plugin->getServer()->getPluginManager()->registerEvents($this, $this->plugin);
	}
	
	public function onJoin(PlayerJoinEvent $event){
		$player = $event->getPlayer();
		$this->create($player);
		$this->setLine(1, $player->getName(), $player);
	}
	/*恐らく送る準備的な??*/
	public function create(Player $player){
		$pk = new SetDisplayObjectivePacket();
		$pk->displaySlot = self::displaySlot;
		$pk->objectiveName = self::objectiveName;
		$pk->displayName = self::displayName;
		$pk->criteriaName = "dummy";
		$pk->sortOrder = self::sortOrder;
		
		$player->dataPacket($pk);
	}
	
	/*消す*/
	public function remove(Player $player){
		$pk = new RemoveObjectPacket();
		$pk->objectiveName = self::objectiveName;
		
		$player->dataPacket($pk);
	}
	
	/*指定した行に文章をセット*/
	public function setLine(int $line, string $message, Player $player){
		$pk = new ScorePacketEntry();
		$pk->objectiveName = self::objectiveName;
		$pk->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
		$pk->customName = $message;
		$pk->score = $line;
		$pk->scoreboardId = $line;
		
		$set = new SetScorePacket();
		$set->type = SetScorePacket::TYPE_CHANGE;
		$set->entries[] = $pk;
		$player->dataPacket($set);
	}
	
	public function removeLine(int $line, Player $player){
		$pk = new ScorePacketEntry();
		$pk->objectiveName = self::objectiveName;
		$pk->score = $line;
		$pk->scoreboardId = $line;
		
		$set = new SetScorePacket();
		$set->type = SetScorePacket::TYPE_REMOVE;
		$set->entries[] = $pk;
		
		$player->dataPacket($set);
	}
}
		
