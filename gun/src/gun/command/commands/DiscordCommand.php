<?php

namespace gun\command\commands;

use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\command\CommandSender;

use gun\provider\DiscordProvider;

class DiscordCommand extends BattleFrontCommand
{
    const NAME = "discord";
    const DESCRIPTION = "Discord連携管理用コマンドです";
    const USAGE = "";

    const PERMISSION = "battlefront.command.discord";

    public function execute(CommandSender $sender, string $label, array $args) : bool
    {
        if(parent::execute($sender, $label, $args) === false) {
            return false;
        }
        
        switch(array_shift($args))
        {
            case "on":
                DiscordProvider::get()->setEnable(true);
                $sender->sendMessage("Discordとの連携を有効にしました");
                return true;

            case "off":
                DiscordProvider::get()->setEnable(false);
                $sender->sendMessage("Discordとの連携を無効にしました");
                return true;

            case "webhook":
                $webhook = array_shift($args);
                if(is_null($webhook))
                {
                    $sender->sendMessage(TextFormat::RED . "Discordとの連携用URLを指定してください");
                    return false;
                }
                DiscordProvider::get()->setWebhook($webhook);
                $sender->sendMessage("Discordとの連携用URLを変更しました");
                return true;

            default:
                $sender->sendMessage("使い方: /discord <on|off|webhook>");
                return true;
        }
    }
}