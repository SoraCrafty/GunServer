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

use gun\fishing\item\FishingRod;

use gun\discord\DiscordManager;

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
	/*DiscordManagerのオブジェクト*/
	public $discordManager;


	public function onLoad()
	{
		ItemFactory::registerItem(new Fireworks(), true);
		Entity::registerEntity(FireworksRocket::class, true);

		ItemFactory::registerItem(new FishingRod(), true);

		Item::initCreativeItems();
	}
	
	public function onEnable(){
		if (!file_exists($this->getDataFolder())) @mkdir($this->getDataFolder(), 0744, true);
		ProviderManager::init($this);
		WeaponManager::init($this);
		CommandManager::init($this);
		FormManager::init($this);
		$this->BossBar = new BossBar($this);
		$this->Fireworks = new FireworksAPI($this);
		self::$datafolder = $this->getDataFolder();
		$this->server = $this->getServer();
		//$this->data = new dataManager($this);
		$this->gameManager = new gameManager($this);
		$this->listener = new Listener($this);
		$this->npcManager = new NPCManager($this);
		$this->discordManager = new DiscordManager($this);
		//$this->scoreboard = new scoreboard\scoreboard($this);
		$this->server->getPluginManager()->registerEvents($this->listener, $this);
		$this->server->getNetwork()->setName("§l§fBattleFront§c2");
	}

	public function onDisable()
	{
		WeaponManager::close();
		ProviderManager::close();
	}
}

	
