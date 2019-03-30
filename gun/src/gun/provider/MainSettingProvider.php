<?php

namespace gun\provider;

use pocketmine\utils\Config;

use gun\game\games\TeamDeathMatch;
use gun\game\games\FlagMatch;

class MainSettingProvider extends Provider
{

    const PROVIDER_ID = "mainsetting";
    /*ファイル名(拡張子はなし)*/
    const FILE_NAME = "mainsetting";
    /*セーブデータのバージョン*/
    const VERSION = 1;
    /*デフォルトデータ*/
    const DATA_DEFAULT = [
    						"GameMode" => FlagMatch::GAME_ID,
                            "Reboot_Count" => 5,
                            "LobbyWorld" => ""
    					];

    public function open()
    {
        parent::open();
        if($this->data["LobbyWorld"] === "") $this->data["LobbyWorld"] = $this->plugin->getServer()->getDefaultLevel()->getFolderName();
    }

    public function getGameMode()
    {
    	return $this->data["GameMode"];
    }

    public function getRebootCount()
    {
        return $this->data["Reboot_Count"];
    }

    public function getLobbyWorldName()
    {
        return $this->data["LobbyWorld"];
    }

    public function getLobbyWorld()
    {
        return $this->plugin->getServer()->getLevelByName($this->data["LobbyWorld"]);
    }

}
