<?php

namespace gun\player;

use pocketmine\Player;

use gun\weapons\WeaponManager;

use gun\scoreboard\ScoreboardManager;

use gun\provider\ProviderManager;
use gun\provider\AccountProvider;
use gun\provider\GuideBookProvider;
use gun\provider\MainSettingProvider;

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

	public function setOS($player, $os)
	{
		if(!$player instanceof Player) $name = $player;
		else $name = $player->getName();
		$this->data[$name]["os"] = $os;
	}

	public function getOS(Player $player)
	{
		$os = null;
		$name = $player->getName();
		if(isset($this->data[$name]["os"])) $os = $this->data[$name]["os"];
		return $os;
	}

	public function isPC(Player $player)
	{
		return $this->getOS($player) === self::OS_WINDOWS;
	}

	public function setLobbyInventory(Player $player)
	{
		$content = [];
		$content[] = GuideBookProvider::get()->getGuideBook();
		$player->getInventory()->setContents($content);
		$armorContent = [];
		$player->getArmorInventory()->setContents([]);
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

	public function setDefaultNameTags(Player $player)
	{
    	$tag = $player->getName();
    	if($player->isOp()) $tag = "§b★§f{$tag}";
    	$player->setNameTag($tag);
    	$player->setDisplayName($tag);
    	$player->setNameTagAlwaysVisible(true);
	}

	public function setDefaultSpawn(Player $player)
	{
		$player->setSpawn(MainSettingProvider::get()->getLobbyWorld()->getSpawnLocation());
	}

	public function gotoLobby(Player $player)
	{
		$player->teleport(MainSettingProvider::get()->getLobbyWorld()->getSpawnLocation());
	}

	public function sendBaseScoreboard(Player $player)
	{
		ScoreboardManager::updateLine($player, ScoreboardManager::LINE_EXP, '§eExp§f : ' . AccountProvider::get()->getExp($player));
		ScoreboardManager::updateLine($player, ScoreboardManager::LINE_POINT, '§6Point§f : ' . AccountProvider::get()->getPoint($player));
		ScoreboardManager::updateLine($player, ScoreboardManager::LINE_KILL, '§cKill§f : ' . AccountProvider::get()->getKill($player));
		ScoreboardManager::updateLine($player, ScoreboardManager::LINE_DEATH, '§4Death§f : ' . AccountProvider::get()->getDeath($player));
		ScoreboardManager::updateLine($player, ScoreboardManager::LINE_KILLRATIO, '§5K/D§f : ' . AccountProvider::get()->getKillRatio($player));
	}

}
