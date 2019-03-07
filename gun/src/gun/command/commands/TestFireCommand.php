<?php

namespace gun\command\commands;

use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\item\WrittenBook;
use pocketmine\command\CommandSender;

use gun\provider\TestFiringFieldProvider;

use gun\weapons\WeaponManager;
use gun\game\GameManager;
use gun\game\games\TeamDeathMatch;

class TestFireCommand extends BattleFrontCommand
{
    const NAME = "testfire";
    const DESCRIPTION = "試し打ち関連コマンドです";
    const USAGE = "";

    const PERMISSION = "battlefront.command.testfire";

    public function execute(CommandSender $sender, string $label, array $args) : bool
    {
        if(parent::execute($sender, $label, $args) === false) {
            return false;
        }
        
        switch(array_shift($args))
        {
            case "go":
                if(!$sender instanceof Player)
                {
                    $sender->sendMessage(TextFormat::RED . "ゲーム内で実行してください");
                    return false;
                }

                $gameObject = GameManager::getObject();
                switch($gameObject->getName())
                {
                    case TeamDeathMatch::GAME_NAME:
                        if($gameObject->isPlayer($sender))
                        {
                            $sender->sendMessage(TextFormat::RED . "今は実行できません");
                            return false;
                        }
                        break;
                }

                $sender->teleport(TestFiringFieldProvider::get()->getPosition());
                WeaponManager::setPermission($this->plugin, $sender, true);
                return true;

            case "back":
                if(!$sender instanceof Player)
                {
                    $sender->sendMessage(TextFormat::RED . "ゲーム内で実行してください");
                    return false;
                }

                $gameObject = GameManager::getObject();
                switch($gameObject->getName())
                {
                    case TeamDeathMatch::GAME_NAME:
                        if($gameObject->isPlayer($sender))
                        {
                            $sender->sendMessage(TextFormat::RED . "今は実行できません");
                            return false;
                        }
                        break;
                }

                $this->plugin->playerManager->gotoLobby($sender);
                WeaponManager::setPermission($this->plugin, $sender, false);
                $this->playerManager->setLobbyInventory($sender);
                return true;

            case "set":
                if(!$sender instanceof Player)
                {
                    $sender->sendMessage(TextFormat::RED . "ゲーム内で実行してください");
                    return false;
                }
                if(!$sender->isOp())
                {
                    $sender->sendMessage(TextFormat::RED . "このコマンドを実行する権限がありません");
                    return false;
                }

                TestFiringFieldProvider::get()->setPosition($sender);
                $sender->sendMessage("試し打ちの初期地点を設定しました");
                return true;

            default:
                $sender->sendMessage("使い方: /testfire <go|back|set>");
                return true;
        }
    }
}