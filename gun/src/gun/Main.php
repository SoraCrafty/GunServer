<?php

namespace gun;

use pocketmine\command\Command as CMD;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;

use pocketmine\Server;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase {
	
	public static $datafolder;
	
	public function onEnable(){
		if (!file_exists($this->getDataFolder())) @mkdir($this->getDataFolder(), 0744, true);
		self::$datafolder = $this->getDataFolder();
		$this->server = $this->getServer();
		$this->data = new dataManager($this);
		$this->listener = new Listener($this);
		$this->game = new gameManager($this);
		$this->npc = new npcManager($this);
		$this->command = new Command($this);
		$this->server->getPluginManager()->registerEvents($this->listener, $this);
	}
	
	
	public function onCommand(CommandSender $sender, CMD $command, $label, array $args):bool {
		$this->command->call($sender, $command, $label, $args);
		return true;
	}
}

	
