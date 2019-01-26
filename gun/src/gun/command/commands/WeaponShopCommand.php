<?php

namespace gun\command\commands;

use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\command\CommandSender;

use gun\form\FormManager;
use gun\form\forms\MainShopForm;

class WeaponShopCommand extends BattleFrontCommand
{
    const NAME = "weaponshop";
    const DESCRIPTION = "武器ショップを開くコマンドです";
    const USAGE = "";

    const PERMISSION = "battlefront.command.weaponshop";

    public function execute(CommandSender $sender, string $label, array $args) : bool
    {
        if(parent::execute($sender, $label, $args) === false) {
            return false;
        }

        if(!$sender instanceof Player){
            $sender->sendMessage(TextFormat::RED . "ゲーム内で実行してください");
            return true;
        }
        
        switch(array_shift($args))
        {
            case "main":
                FormManager::register(new MainShopForm($this->plugin, $sender));
                return true;
            default:
                $sender->sendMessage("使用法 : /weaponshop <main>");
                return true;
        }
    }
}