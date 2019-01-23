<?php

namespace gun\command\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use gun\Main;

class BattleFrontCommand extends Command
{
    const NAME = "";
    const DESCRIPTION = "";
    const USAGE = "";

    const PERMISSION = "";

    /*Mainクラスのオブジェクト*/
    protected $plugin;

    public function __construct(Main $plugin)
    {
        parent::__construct(static::NAME, static::DESCRIPTION, static::USAGE);

        $this->setPermission(static::PERMISSION);

        $this->plugin = $plugin;
    }


    public function execute(CommandSender $sender, string $label, array $args) : bool
    {
        if(!$this->plugin->isEnabled())
        {
            return false;
        }
        if(!$this->testPermission($sender))
        {
            return false;
        }

        return true;
    }
}
