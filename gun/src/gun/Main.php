<?php

namespace gun;

use pocketmine\Server;
use pocketmine\plugin\PluginBase;

use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;

use gun\bossbar\BossBarManager;

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

use gun\player\PlayerManager;

use gun\game\GameManager;

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
	/*PlayerManagerのオブジェクト*/
	public $playerManager;

	public function onLoad()
	{
		ItemFactory::registerItem(new Fireworks(), true);
		Entity::registerEntity(FireworksRocket::class, true);

		ItemFactory::registerItem(new FishingRod(), true);

		Item::initCreativeItems();
	}
	
	public function onEnable(){
		if (!file_exists($this->getDataFolder())) @mkdir($this->getDataFolder(), 0744, true);
		date_default_timezone_set('Asia/Tokyo');
		ProviderManager::init($this);
		WeaponManager::init($this);
		CommandManager::init($this);
		FormManager::init($this);
		BossBarManager::init($this);
		GameManager::init($this);
		$this->Fireworks = new FireworksAPI($this);
		self::$datafolder = $this->getDataFolder();
		$this->server = $this->getServer();
		$this->listener = new Listener($this);
		$this->npcManager = new NPCManager($this);
		$this->discordManager = new DiscordManager($this);
		$this->playerManager = new PlayerManager($this);
		$this->scoreboard = new scoreboard\scoreboard($this);
		$this->server->getPluginManager()->registerEvents($this->listener, $this);
		$this->server->getNetwork()->setName("§l§fBattleFront§c2");

		$this->discordManager->sendMessage('**❗サーバーが`' . GameManager::getObject()->getName() . '`モードで起動しました  **(' . date("m/d H:i") . ')');
		$this->getScheduler()->scheduleRepeatingTask(new RebootTask($this), 20);
	}

	public function onDisable()
	{
		$this->discordManager->sendMessageDirect('**❗サーバーが停止しました  **(' . date("m/d H:i") . ')');

		WeaponManager::close();
		ProviderManager::close();
	}
}

	
