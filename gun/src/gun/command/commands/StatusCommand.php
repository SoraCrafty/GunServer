<?php

namespace gun\command\commands;

use pocketmine\Player;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

use gun\form\forms\StatusForm;
use gun\form\FormManager;

class StatusCommand extends BattleFrontCommand
{
	const NAME = "mystatus";
    const DESCRIPTION = "ステータス確認用コマンドです";
    const USAGE = "";

    const PERMISSION = "battlefront.command.mystatus";

    public function execute(CommandSender $sender, string $label, array $args): bool 
    {
    	if(parent::execute($sender, $label, $args) === false) {
            return false;
        }

        switch($label)
        {
        	case "mystatus":
        	if(!$sender instanceof Player)
                {
                    $sender->sendMessage(TextFormat::RED . "ゲーム内で実行してください");
                    return false;
                }
        	FormManager::register(new StatusForm($this->plugin, $sender));
        	return true;
        }
        return true;

    }
}