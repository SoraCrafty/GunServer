<?php

namespace gun\command\commands;

use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\command\CommandSender;

use gun\provider\DiscordProvider;

use pocketmine\entity\Entity;

use gun\entity\target\Target;

class SummonCommand extends BattleFrontCommand
{
    const NAME = "summon";
    const DESCRIPTION = "Entity召喚用コマンドです";
    const USAGE = "";

    const PERMISSION = "battlefront.command.summon";

    public function execute(CommandSender $sender, string $label, array $args) : bool
    {
        if(parent::execute($sender, $label, $args) === false) {
            return false;
        }

        if(!$sender instanceof Player)
        {
            $sender->sendMessage(TextFormat::RED . "ゲーム内で実行してください");
            return false;
        }
        
        switch(array_shift($args))
        {
            case "target":
                switch($sender->getDirection())
                {
                    case 0:
                        $yaw = 90;
                        break;
                    case 1:
                        $yaw = 180;
                        break;
                    case 2:
                        $yaw = 270;
                        break;
                    case 3:
                        $yaw = 360;
                        break;
                }
                $nbt = Entity::createBaseNBT(
                    $sender,
                    $sender->getMotion(),
                    $yaw,
                    0
                );
                $entity = new Target($sender->getLevel(), $nbt);
                $entity->spawnToAll();      
                return true;

            default:
                $sender->sendMessage("使い方: /summon <target>");
                return true;
        }
    }
}