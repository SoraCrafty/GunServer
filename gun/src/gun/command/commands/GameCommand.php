<?php

namespace gun\command\commands;

use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\command\CommandSender;

class GameCommand extends BattleFrontCommand
{
    const NAME = "game";
    const DESCRIPTION = "ゲーム用コマンドです";
    const USAGE = "";

    const PERMISSION = "battlefront.command.game";

    public function execute(CommandSender $sender, string $label, array $args) : bool
    {
        if(parent::execute($sender, $label, $args) === false) {
            return false;
        }
        
        switch(array_shift($args))
        {
            case "join":
                if(!$sender instanceof Player)
                {
                    $sender->sendMessage(TextFormat::RED . "ゲーム内で実行してください");
                    return false;
                }
                if($this->plugin->gameManager->isGaming())
                {
                    $team = $this->plugin->gameManager->getTeam($sender);
                    if($team === false)
                    {
                        $this->plugin->gameManager->lotteryTeam($sender);
                        $team = $this->plugin->gameManager->getTeam($sender);
                    }
                    else
                    {
                        $this->plugin->gameManager->setTeam($sender, $team);
                    }
                    $this->plugin->gameManager->setSpawn($sender, $team);
                    $this->plugin->gameManager->gotoStage($sender, $team);
                    $this->plugin->gameManager->setNameTags($sender, $team);
                    $this->plugin->gameManager->setInventory($sender);
                    $this->plugin->gameManager->setHealth($sender);
                }
                return true;

            default:
                $sender->sendMessage("使い方: /game <join>");
                return true;
        }
    }
}