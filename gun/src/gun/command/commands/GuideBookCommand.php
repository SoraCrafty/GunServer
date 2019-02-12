<?php

namespace gun\command\commands;

use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\item\WrittenBook;
use pocketmine\command\CommandSender;

use gun\provider\GuideBookProvider;

class GuideBookCommand extends BattleFrontCommand
{
    const NAME = "guidebook";
    const DESCRIPTION = "ガイドブック管理用コマンドです";
    const USAGE = "";

    const PERMISSION = "battlefront.command.guidebook";

    public function execute(CommandSender $sender, string $label, array $args) : bool
    {
        if(parent::execute($sender, $label, $args) === false) {
            return false;
        }
        
        switch(array_shift($args))
        {
            case "set":
                if(!$sender instanceof Player)
                {
                    $sender->sendMessage(TextFormat::RED . "ゲーム内で実行してください");
                    return false;
                }
                $item = $sender->getInventory()->getItemInHand();
                if(!$item instanceof WrittenBook)
                {
                    $sender->sendMessage(TextFormat::RED . "ガイドブックとして設定したい本を手に持ってください");
                    return false;
                }
                GuideBookProvider::get()->setGuideBook($item);
                $sender->sendMessage("ガイドブックを設定しました");
                return true;

            case "get":
                if(!$sender instanceof Player)
                {
                    $sender->sendMessage(TextFormat::RED . "ゲーム内で実行してください");
                    return false;
                }
                $sender->getInventory()->addItem(GuideBookProvider::get()->getWritableGuideBook());
                return true;

            default:
                $sender->sendMessage("使い方: /guidebook <set|get>");
                return true;
        }
    }
}