<?php
namespace gun\npc;

use pocketmine\event\Listener;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\server\DataPacketReceiveEvent;

use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;

class NPCManager implements Listener
{

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

	public function onPlayerJoin(PlayerJoinEvent $event)
	{
		foreach($this->npc as $npc)
		{
			if($npc->getLevel()->getFolderName() === $event->getPlayer()->getLevel()->getFolderName())
			{
				$npc->spawnTo($event->getPlayer());
			}
		}
	}

	public function onPlayerMove(PlayerMoveEvent $event)
	{
		$player = $event->getPlayer();

		foreach($this->npc as $npc)
		{
			if($npc->isGazer() && $npc->getLevel()->getFolderName() === $event->getPlayer()->getLevel()->getFolderName())
			{
				$npc->gazeAt($player);
			}
		}
	}

	public function onEntityTeleport(EntityTeleportEvent $event)
	{
		$player = $event->getEntity();

		if($player instanceof Player)
		{
			if($event->getFrom()->getLevel()->getFolderName() !== ($toLevel = $event->getTo()->getLevel()->getFolderName()))
			{
				foreach($this->npc as $npc)
				{
					if($npc->getLevel()->getFolderName() === $toLevel)
					{
						$npc->spawnTo($player);
					}
					else
					{
						$npc->despawnFrom($player);
					}
				}
			}
		}
	}

	public function onPacketReceived(DataPacketReceiveEvent $event){
		$pk = $event->getPacket();
		if($pk instanceof InventoryTransactionPacket and $pk->transactionType === InventoryTransactionPacket::TYPE_USE_ITEM_ON_ENTITY){
			if(isset($this->npc[$pk->trData->entityRuntimeId])){
				$npc = $this->npc[$pk->trData->entityRuntimeId];
				$npc->onTouch($event->getPlayer());
			}
		}
	}

}
