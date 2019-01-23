<?php

namespace gun\command\commands;

use pocketmine\utils\TextFormat;
use pocketmine\command\CommandSender;

class NPCCommand extends BattleFrontCommand
{
    const NAME = "npc";
    const DESCRIPTION = "NPC管理コマンドです";
    const USAGE = "";

    const PERMISSION = "battlefront.command.npc:";

    public function execute(CommandSender $sender, string $label, array $args) : bool
    {
        if(parent::execute($sender, $label, $args) === false) {
            return false;
        }
        $this->plugin->npcManager->onCommand($sender, $this, $label, $args);
        return true;
    }
}