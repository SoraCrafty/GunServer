<?php
namespace gun\ranking;

use pocketmine\Player;

use pocketmine\utils\UUID;

use pocketmine\item\Item;

use pocketmine\entity\Entity;

use pocketmine\math\Vector3;

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\Listener;

use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\RemoveEntityPacket;

use gun\provider\RankingProvider;
use gun\provider\AccountProvider;

class Ranking implements Listener{

	private static $instance;
	
	public function __construct($plugin) {
		$this->plugin = $plugin;
		$this->uuid = UUID::fromRandom();
		$this->eid = 7777777;
		$this->plugin->getServer()->getPluginManager()->registerEvents($this, $this->plugin);
		self::$instance = $this;
		$this->Ranking();
	}
	
	public function Ranking(){
		$playerdata = AccountProvider::get()->getData();
		foreach($playerdata as $name => $data){
			if($data['death'] === 0){
				$rank[$name] = 0;
			}else{
				$rank[$name] = $data['kill']/$data['death'];
			}
		}
		if(!isset($rank)) return false;
		for ($e = 10; $e > 0; $e--) {
			if (count($rank) > 0) {
				$na = array_search(max($rank), $rank);
				$this->data[$na] = max($rank);
				unset($rank[$na]);
			}
		}
		$i = 1;
		$this->name = "§eキルレランキング§f\n";
		foreach($this->data as $name => $amount){
			$padname = str_pad($name, 15, ' ', STR_PAD_BOTH);
			$this->name  .= "{$i}位 {$padname} {$amount}\n";
			$i++;
		}
		foreach($this->plugin->getServer()->getOnlinePlayers() as $player){
			$this->remove($player);
			$this->spawnTo($player);
		}
	}
	
	public function spawnTo(Player $player){
		$pk = new AddPlayerPacket();
		$pk->uuid = $this->uuid;
		$pk->username = $this->name;
		$pk->entityRuntimeId = $this->eid;
		$position = RankingProvider::get()->getPosition();
		$pk->position = new Vector3($position['x'], $position['y'], $position['z']);
		$pk->motion = new Vector3(0, 0, 0);
		$pk->yaw = 0;
		$pk->pitch = 0;
		$pk->item = Item::get(0, 0, 0);
		$flags = (
			(1 << Entity::DATA_FLAG_CAN_SHOW_NAMETAG) |
			(1 << Entity::DATA_FLAG_ALWAYS_SHOW_NAMETAG) |
			(1 << Entity::DATA_FLAG_NO_AI)
		);
		$pk->metadata = [ 
			Entity::DATA_FLAG_INVISIBLE => [Entity::DATA_TYPE_BYTE, 0],
			Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 0.01],
			Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING,$this->name],
			Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, $flags],
		];

		$player->dataPacket($pk);
	}
	
	public function remove(Player $player){
		$pk = new RemoveEntityPacket();
		$pk->entityUniqueId = $this->eid;
		$player->dataPacket($pk);
	}
	
	public function onJoin(PlayerJoinEvent $event){
		$player = $event->getPlayer();
		$this->spawnTo($player);
	}
	
	public static function get(){
		return self::$instance;
	}
}
	
	
