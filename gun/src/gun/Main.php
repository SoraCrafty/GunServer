<?php

namespace gun;

use pocketmine\command\Command as CMD;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;

use pocketmine\Server;
use pocketmine\plugin\PluginBase;

use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;

use gun\bossbar\BossBar;

use gun\fireworks\FireworksAPI;
use gun\fireworks\item\Fireworks;
use gun\fireworks\entity\FireworksRocket;

use gun\npc\NPCManager;

class Main extends PluginBase {
	
	public static $datafolder;

	/*BossBarのAPIのオブジェクト*/
	public $BossBar;
	/*FireworksAPIのオブジェクト*/
	public $Fireworks;
	/*gameManagerのオブジェクト*/
	public $gameManager;
	/*NPCManagerのオブジェクト*/
	public $npcManager;

	public function onLoad()
	{
		ItemFactory::registerItem(new Fireworks(), true);
		Entity::registerEntity(FireworksRocket::class, true);
	}
	
	public function onEnable(){
		if (!file_exists($this->getDataFolder())) @mkdir($this->getDataFolder(), 0744, true);
		$this->BossBar = new BossBar($this);
		$this->Fireworks = new FireworksAPI($this);
		self::$datafolder = $this->getDataFolder();
		$this->server = $this->getServer();
		$this->data = new dataManager($this);
		$this->gameManager = new gameManager($this);
		$this->listener = new Listener($this);
		$this->npcManager = new NPCManager($this);
		$this->command = new Command($this);
		$this->scoreboard = new scoreboard\scoreboard($this);
		$this->server->getPluginManager()->registerEvents($this->listener, $this);
	}
	
	public function onCommand(CommandSender $sender, CMD $command, $label, array $args):bool {
		$this->command->call($sender, $command, $label, $args);
		return true;
	}
}

	
