<?php

namespace gun\command\commands;

use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\command\CommandSender;

use gun\weapons\WeaponManager;

class WeaponCommand extends BattleFrontCommand
{
    const NAME = "weapon";
    const DESCRIPTION = "武器管理コマンドです";
    const USAGE = "";

    const PERMISSION = "battlefront.command.weapon";

    public function execute(CommandSender $sender, string $label, array $args) : bool
    {
        if(parent::execute($sender, $label, $args) === false) {
            return false;
        }
        
        switch(array_shift($args))
        {
            case "list":
                $data = WeaponManager::getAllData(array_shift($args));
                if(is_null($data))
                {
                    $text = "使い方: /weapon <";
                    foreach (WeaponManager::getIds() as  $id) {
                        $text .= "{$id}|";
                    }
                    $text .= ">";
                    $sender->sendMessage($text);
                    return true;
                }

                $text = "武器一覧▼\n";
                foreach ($data as $key => $value) {
                    $text .= "§r§aID:§f{$key} §aName:§f{$value["Item_Information"]["Item_Name"]}\n";
                }
                $sender->sendMessage($text);
                return true;

            case "get":
                if(!$sender instanceof Player){
                    $sender->sendMessage(TextFormat::RED . "ゲーム内で実行してください");
                    return true;
                }

                $weapon = WeaponManager::get(array_shift($args), array_shift($args));
                if(is_null($weapon))
                {
                    $sender->sendMessage("使い方: /weapon get <type> <id>\n▶typeとidは/weapon list を使用すると確認できます");
                    return true;
                }
                $sender->getInventory()->addItem($weapon);
                return true;

            default:
                $sender->sendMessage("使い方: /weapon <list|get>");
                return true;
        }
    }
}