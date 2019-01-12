<?php
namespace gun\npc;

use pocketmine\Player;

use pocketmine\utils\TextFormat;

use pocketmine\event\Listener;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\server\DataPacketReceiveEvent;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;

class NPCManager implements Listener//後々単体でプラグイン化したいのでそんな感じに設計したい
{

	/*Mainクラスのオブジェクト*/
	private $plugin;
	/*NPCオブジェクトの配列*/
	private $npcs = [];

	//コマンド系
	/*NPCを削除するための変数*/
	private $deleteQueue =[];
	/*名前を設定するための変数*/
	private $nameQueue =[];
	/*サイズを設定するための変数*/
	private $sizeQueue =[];
	/*メッセージを設定するための変数*/
	private $messageQueue =[];
	/*コマンドを設定するための変数*/
	private $commandQueue =[];

	public function __construct($plugin)
	{
		$this->plugin = $plugin;

		if(!file_exists($this->plugin->getDataFolder())){
			mkdir($this->plugin->getDataFolder());
		}

		if(!file_exists($this->plugin->getDataFolder() . "npcs")){
			mkdir($this->plugin->getDataFolder() . "npcs");
		}

		if(!file_exists($this->plugin->getDataFolder() . "skins")){
			mkdir($this->plugin->getDataFolder() . "skins");
		}

		$dir = scandir($this->plugin->getDataFolder()."npcs/");

		$this->plugin->getServer()->getPluginManager()->registerEvents($this, $this->plugin);//NPCも部品化的なことして再利用したかったので…すみません
	}

	public function onCommand(CommandSender $sender, Command $command, $label, array $args) : bool
	{
		switch(array_shift($args))
		{
			case "create":
				if(!$sender instanceof Player){
					$sender->sendMessage(TextFormat::RED . "ゲーム内で実行してください");
					return true;
				}

				switch(array_shift($args))
				{
					case "normal":
					case NPC::TYPE:
						$npc = NPC::fromPlayerObject($sender, $this->plugin);
						break;
					case "message":
					case MessageNPC::TYPE:
						$npc = MessageNPC::fromPlayerObject($sender, $this->plugin);
						break;
					case "command":
					case CommandNPC::TYPE:
						$npc = CommandNPC::fromPlayerObject($sender, $this->plugin);
						break;
					default:
						$sender->sendMessage("使い方: /npc create <normal|message|command>");
						return true;
				}

				$this->npcs[$npc->getId()] = $npc;
				$npc->spawn();
				return true;

			case "delete":
				if(!$sender instanceof Player){
					$sender->sendMessage(TextFormat::RED . "ゲーム内で実行してください");
					return true;
				}

				$this->deleteQueue[] = $sender->getName();
				$sender->sendMessage("削除したいNPCをタッチしてください");
				return true;

			case "name":
				if(!$sender instanceof Player){
					$sender->sendMessage(TextFormat::RED . "ゲーム内で実行してください");
					return true;
				}

				$name = str_replace("改行", "\n", implode(" ", $args));
				if(trim($name) === "")
				{
					$sender->sendMessage(TextFormat::RED . "使い方: /npc name <設定したい名前>");
					return true;
				}

				$this->nameQueue[$sender->getName()] = $name;
				$sender->sendMessage("名前を設定したいNPCをタッチしてください");
				return true;

			case "size":
				if(!$sender instanceof Player){
					$sender->sendMessage(TextFormat::RED . "ゲーム内で実行してください");
					return true;
				}

				$size = array_shift($args);

				if(!is_numeric($size)){
					$sender->sendMessage(TextFormat::RED . "使い方: /npc size <NPCの大きさ>");
					return true;
				}

				$this->sizeQueue[$sender->getName()] = $size;
				$sender->sendMessage("大きさを設定したいNPCをタッチしてください");
				return true;

			case "message":
				if(!$sender instanceof Player){
					$sender->sendMessage(TextFormat::RED . "ゲーム内で実行してください");
					return true;
				}

				switch(array_shift($args))
				{
					case "list":
						$this->messageQueue[$sender->getName()] = [
																"type" => "list"
																];
						$sender->sendMessage("メッセージの確認をしたいNPCをタッチしてください");
						return true;

					case "add":
						$message = str_replace("改行", "\n", implode(" ", $args));
						if(trim($message) === "")
						{
							$sender->sendMessage(TextFormat::RED . "使い方: /npc message add <追加したいメッセージ>");
							return true;
						}
						$this->messageQueue[$sender->getName()] = [
																"type" => "add",
																"message" => $message
																];
						$sender->sendMessage("メッセージの追加をしたいNPCをタッチしてください");
						return true;

					case "delete":
						$key = array_shift($args);
						if(is_null($key))
						{
							$sender->sendMessage(TextFormat::RED . "使い方: /npc message delete <削除したいメッセージのキー>");
							return true;
						}

						$this->messageQueue[$sender->getName()] = [
																"type" => "delete",
																"key" => $key
																];
						$sender->sendMessage("メッセージを削除したいNPCをタッチしてください");
						return true;

					default:
						$sender->sendMessage(TextFormat::RED . "使い方: /npc message <list|add|delete>");
						return true;
				}

				return true;

			case "command":
				if(!$sender instanceof Player){
					$sender->sendMessage(TextFormat::RED . "ゲーム内で実行してください");
					return true;
				}

				$command = str_replace("改行", "\n", implode(" ", $args));
				if(trim($command) === "")
				{
					$sender->sendMessage(TextFormat::RED . "使い方: /npc command <設定したいコマンド>");
					return true;
				}

				$this->commandQueue[$sender->getName()] = $command;
				$sender->sendMessage("コマンドを設定したいNPCをタッチしてください");
				return true;

			default:
				$sender->sendMessage(TextFormat::RED . "使い方: /npc <create|delete|name|size|message|command>");
				return true;
		}
	}

	public function onPlayerJoin(PlayerJoinEvent $event)
	{
		foreach($this->npcs as $npc)
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

		foreach($this->npcs as $npc)
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
				foreach($this->npcs as $npc)
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
		if($pk instanceof InventoryTransactionPacket && $pk->transactionType === InventoryTransactionPacket::TYPE_USE_ITEM_ON_ENTITY){
			if(isset($this->npcs[$pk->trData->entityRuntimeId])){

				$npc = $this->npcs[$pk->trData->entityRuntimeId];

				$player = $event->getPlayer();
				$name = $player->getName();
				if(!isset($this->nameQueue[$name]) && !in_array($name, $this->deleteQueue) && !isset($this->sizeQueue[$name]) && !isset($this->messageQueue[$name]) && !isset($this->commandQueue[$name]))
				{
					$npc->onTouch($player);
					return true;
				}

				if(in_array($name, $this->deleteQueue))
				{
					$npc->despawn();
					unset($this->npcs[$pk->trData->entityRuntimeId]);
					unset($this->deleteQueue[array_search($name, $this->deleteQueue)]);
					$player->sendMessage(TextFormat::GREEN . "NPCを削除しました");
					return true;
				}

				if(isset($this->nameQueue[$name]))
				{
					$npc->setName($this->nameQueue[$name]);
					unset($this->nameQueue[$name]);
					$player->sendMessage(TextFormat::GREEN . "名前を設定しました");
				}

				if(isset($this->sizeQueue[$name]))
				{
					$npc->setSize($this->sizeQueue[$name]);
					unset($this->sizeQueue[$name]);
					$player->sendMessage(TextFormat::GREEN . "大きさを設定しました");
				}

				if(isset($this->messageQueue[$name]))
				{
					if($npc->getType() !== MessageNPC::TYPE)
					{
						$player->sendMessage(TextFormat::RED . "このNPCはメッセージタイプのNPCではありません");
					}
					else
					{
						switch($this->messageQueue[$name]["type"])
						{
							case "list":
								$text = "--このNPCに設定されているメッセージ一覧--";
								foreach ($npc->getMessages() as $key => $message) {
									$text .= "\nKey{$key} : {$message}";
								}
								$player->sendMessage($text);
								break;
							case "add":
								$npc->addMessage($this->messageQueue[$name]["message"]);
								$player->sendMessage(TextFormat::GREEN . "メッセージを追加しました");
								break;
							case "delete":
								if(!$npc->isExist($this->messageQueue[$name]["key"]))
								{
									$player->sendMessage(TextFormat::RED . "指定したキーにメッセージが存在しません");
									break;
								}
								$npc->deleteMessage($this->messageQueue[$name]["key"]);
								$player->sendMessage(TextFormat::GREEN . "指定したキーのメッセージを削除しました");
								break;
						}
					}

					unset($this->messageQueue[$name]);
				}

				if(isset($this->commandQueue[$name]))
				{
					if($npc->getType() !== CommandNPC::TYPE)
					{
						$player->sendMessage(TextFormat::RED . "このNPCはコマンドタイプのNPCではありません");
					}
					else
					{
						$npc->setCommand($this->commandQueue[$name]);
						unset($this->commandQueue[$name]);
						$player->sendMessage(TextFormat::GREEN . "コマンドを設定しました");
					}
				}
			}
		}
	}

}
