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
                            "webhook" => [
                                            "game" => "",
                                        ]
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

    public function getWebhook($channel = "server_chat")
    {
    	return $this->data["webhook"][$channel];
    }

    public function setWebhook($webhook, $channel = "server_chat")
    {
        $this->data["webhook"][$channel] = $webhook;
    }

}
























