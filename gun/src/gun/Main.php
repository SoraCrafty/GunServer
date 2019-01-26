<?php

namespace gun;

use pocketmine\Server;
use pocketmine\plugin\PluginBase;

use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;

use gun\bossbar\BossBar;

use gun\fireworks\FireworksAPI;
use gun\fireworks\item\Fireworks;
use gun\fireworks\entity\FireworksRocket;

use gun\form\FormManager;

use gun\weapons\WeaponManager;
use gun\weapons\WeaponListener;

use gun\npc\NPCManager;
use gun\command\CommandManager;

use gun\provider\ProviderManager;

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
		Item::initCreativeItems();
	}
	
	public function onEnable(){
		if (!file_exists($this->getDataFolder())) @mkdir($this->getDataFolder(), 0744, true);
		WeaponManager::init($this);
		CommandManager::init($this);
		FormManager::init($this);
		ProviderManager::init($this);
		$this->BossBar = new BossBar($this);
		$this->Fireworks = new FireworksAPI($this);
		self::$datafolder = $this->getDataFolder();
		$this->server = $this->getServer();
		//$this->data = new dataManager($this);
		$this->gameManager = new gameManager($this);
		$this->listener = new Listener($this);
		$this->npcManager = new NPCManager($this);
		//$this->scoreboard = new scoreboard\scoreboard($this);
		$this->server->getPluginManager()->registerEvents($this->listener, $this);
	}
}

	
