<?php

namespace gun\command\commands;

use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\command\CommandSender;

use gun\game\GameManager;

class GameCommand extends BattleFrontCommand
{
    const NAME = "game";
    const DESCRIPTION = "ゲーム用コマンドです";
    const USAGE = "";

    const PERMISSION = "battlefront.command.game";

    public function execute(CommandSender $sender, string $label, array $args) : bool
    {
        if(parent::execute($sender, $label, $args) === false)
        {
            return false;
        }
    }
}