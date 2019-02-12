<?php

namespace gun\player;

use pocketmine\Player;

use gun\weapons\WeaponManager;

use gun\provider\ProviderManager;
use gun\provider\AccountProvider;
use gun\provider\GuideBookProvider;

class PlayerManager
{
	/*OSの番号(https://github.com/TuranicTeam/Altay/blob/master/src/pocketmine/Player.phpより引用)*/
	const OS_ANDROID = 1;
	const OS_IOS = 2;
	const OS_MAC = 3;
	const OS_FIREOS = 4;
	const OS_GEARVR = 5;
	const OS_HOLOLENS = 6;
	const OS_WINDOWS = 7;
	const OS_WIN32 = 8;
	const OS_DEDICATED = 9;
	const OS_ORBIS = 10;
	const OS_NX = 11;

	/*メインクラスのオブジェクト*/
	private $plugin;
	/*プレイヤーの一時保存データ*/
	private $data = [];

	public function __construct($plugin)
	{
		$this->plugin = $plugin;
		$this->plugin->getServer()->getPluginManager()->registerEvents(new PlayerManagerListener($plugin, $this), $plugin);
	}

	public function unsetData(Player $player)
	{
		unset($this->data[$player->getName()]);
	}

	public function setOS(Player $player, $os)
	{
		$this->data[$player->getName()]["os"] = $os;
	}

	public function getOS(Player $player)
	{
		$os = null;
		$name = $player->getName();
		if(isset($this->data[$name]["os"])) $os = $this->data[$name]["os"];
		return $os;
	}

	public function setLobbyInventory(Player $player)
	{
		$content = [];
		$content[] = ProviderManager::get(GuideBookProvider::PROVIDER_ID)->getGuideBook();
		$player->getInventory()->setContents($content);
	}

	public function getMainWeapon(Player $player)
	{
		$data = AccountProvider::get()->getMainWeaponData($player);
		return WeaponManager::get($data["type"], $data["id"]);
	}

	public function getSubWeapons(Player $player)
	{
		$weapons = [];
		$data = AccountProvider::get()->getSubWeaponData($player, 0);
		$weapons[] = WeaponManager::get($data["type"], $data["id"]);
		return $weapons;
	}

	public function setDefaultHealth(Player $player)
	{
		$player->setMaxHealth(20);
		$player->setHealth(20);
	}

}
