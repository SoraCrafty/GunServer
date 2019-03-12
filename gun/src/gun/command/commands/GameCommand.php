<?php

namespace gun\command\commands;

use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\command\CommandSender;

use gun\form\FormManager;
use gun\form\forms\GameSettingForm;

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

        if(!$sender instanceof Player)
        {
            $sender->sendMessage(TextFormat::RED . "ゲーム内で実行してください");
            return false;
        }
        
        switch(array_shift($args))
        {
            case 'setting':
                FormManager::register(new GameSettingForm($this->plugin, $sender));
                return true;
        }

    }
}