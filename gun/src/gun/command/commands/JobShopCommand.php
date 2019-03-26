<?php
namespace gun\command\commands;

use pocketmine\command\CommandSender;
use pocketmine\Player;

use gun\form\FormManager;
use gun\form\forms\JobShopForm;

class JobShopCommand extends BattleFrontCommand {

	const NAME = "jobshop";
	const DESCRIPTION = "武器ショップを開くコマンドです";
	const USAGE = "";
	
	const PERMISSION = "battlefront.command.jobshop";
	
	public function execute(CommandSender $sender, string $label, array $args) : bool {
		if(parent::execute($sender, $label, $args) === false) {
			return false;
		}
		
		if(!$sender instanceof Player){
			$sender->sendMessage('コンソールからは実行できません');
			return true;
		}
		
		FormManager::register(new JobShopForm($this->plugin, $sender));
		return true;
	}
}
