<?php

namespace gun\command\commands;

use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\command\CommandSender;

use gun\provider\RankingProvider;
use gun\ranking\Ranking;

class RankingCommand extends BattleFrontCommand
{
    const NAME = "ranking";
    const DESCRIPTION = "ランキング用のコマンドです";
    const USAGE = "";

    const PERMISSION = "battlefront.command.ranking";

    public function execute(CommandSender $sender, string $label, array $args) : bool
    {
        if(parent::execute($sender, $label, $args) === false) {
            return false;
        }
        RankingProvider::get()->setPosition($sender->getPosition());
        Ranking::get()->Ranking();
        $sender->sendMessage('セット完了しました');
        return true;
    }
}
