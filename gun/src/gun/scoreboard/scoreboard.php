<?php
namespace gun\scoreboard;

use pocketmine\Player;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\Listener;
use pocketmine\network\mcpe\protocol\ { SetScorePacket, RemoveObjectivePacket, SetDisplayObjectivePacket };
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;

use gun\Provider\AccountProvider;

class scoreboard implements Listener{
	
	//表示する場所
	const displaySlot = 'sidebar';
	//分からんけど多分id的なものだと思う
	const objectiveName = 'gunserver';
	//スコアボードのタイトル
	const displayName = 'BattleFront§c2§f';
	//並べ方 0が昇順 1が降順
	const sortOrder = 0;
	
	const placeLine = ['exp' => 1, 'kill' => 2, 'death' => 3, 'point' => 4, 'killratio' => 5];
	const deco = ['exp' => '§e', 'kill' => '§c', 'death' => '§a', 'point' => '§d', 'killratio' => '§b'];
	
	private static $instance;
	
	public function __construct($plugin){
		$this->plugin = $plugin;
		$this->plugin->getServer()->getPluginManager()->registerEvents($this, $this->plugin);
		self::$instance = $this;
	}
	
	public static function getScoreBoard(){
		return self::$instance;
	}
	
	public function onJoin(PlayerJoinEvent $event){
		$player = $event->getPlayer();
		$this->create($player);	
		$this->showThisServerScoreBoard($player);
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
							/*行の長さを揃えるため*/
		$pk->customName = str_pad("・" . $message, ((strlen(self::displayName) * 2) - strlen($message)));
		$pk->score = $line;
		$pk->scoreboardId = $line;
		
		$set = new SetScorePacket();
		$set->type = SetScorePacket::TYPE_CHANGE;
		$set->entries[] = $pk;
		$player->dataPacket($set);
	}
	
	/*指定行の文章をリセット*/
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
	
	/*プレイヤーにスコアボードを表示*/
	public function showThisServerScoreBoard($player){
		$data = AccountProvider::get()->getAll($player);
		foreach(array_keys(self::placeLine) as $key){
			$this->updateScoreBoard($key, $data[$key], $player);
		}
	}
			
	/*playerdataが書き換えられたときに呼び出し*/
	public function updateScoreBoard(string $place, $data, Player $player){
		$line = self::placeLine[$place];
		$deco = self::deco[$place];
		$marge = "{$deco}{$place} : {$data}";
		$this->removeLine($line, $player);
		$this->setLine($line, $marge, $player);
		
	}
}
		
