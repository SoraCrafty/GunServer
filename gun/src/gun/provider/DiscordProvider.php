<?php

namespace gun\provider;

use pocketmine\utils\Config;

class DiscordProvider extends Provider
{

    const PROVIDER_ID = "discord";
    /*ファイル名(拡張子はなし)*/
    const FILE_NAME = "discord";
    /*セーブデータのバージョン*/
    const VERSION = 1;
    /*デフォルトデータ*/
    const DATA_DEFAULT = [
                            "enable" => false,
                            "username" => "BattleFront2",
                            "webhook" => ""
    					];

    public function isEnable()
    {
    	return $this->data["enable"];
    }

    public function setEnable(bool $value)
    {
        $this->data["enable"] = $value;
    }

    public function getUserName()
    {
        return $this->data["username"];
    }

    public function getWebhook()
    {
    	return $this->data["webhook"];
    }

    public function setWebhook($webhook)
    {
        $this->data["webhook"] = $webhook;
    }

}
























