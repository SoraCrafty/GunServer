<?php
namespace gun\npc;

use pocketmine\event\Listener;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\server\DataPacketReceiveEvent;

class NPCManager implements Listener{

	/*Mainクラスのオブジェクト*/
	private $plugin;
	/*NPCオブジェクトの配列*/
	private $npcs = [];

	public function __construct($plugin)
	{
		$this->plugin = $plugin;

		if(!file_exists($this->plugin->getDataFolder() . "npc")){
			mkdir($this->plugin->getDataFolder() . "npc");
		}

		$this->plugin->getServer()->getPluginManager()->registerEvents($this, $this->plugin);//NPCも部品化的なことして再利用したかったので…すみません
	}

}
