<?php

namespace gun\command;

use pocketmine\Server;

use gun\command\commands\NPCCommand;
use gun\command\commands\WeaponCommand;
use gun\command\commands\WeaponShopCommand;

class CommandManager{

    public static function init($plugin)
    {
        $map = $plugin->getServer()->getCommandMap();
        $map->register("battlefront", new NPCCommand($plugin));
        $map->register("battlefront", new WeaponCommand($plugin));
        $map->register("battlefront", new WeaponShopCommand($plugin));
    }

}
