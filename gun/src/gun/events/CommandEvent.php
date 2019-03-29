<?php

namespace gun\events;

use pocketmine\Player;

class CommandEvent extends Events{

	public function call($event)
	{
		$sender = $event->getSender();
		$command = $event->getCommand();
		$commandArray = explode(" ", $command);

		switch($commandArray[0])
		{

			case "help":
				if($sender instanceof Player && !$sender->isOp())
				{
					$event->setCencelled(true);
					$sender->sendMessage("§cこのコマンドを実行する権限がありません");
				}
				break;

			case "say":
			case "me":
				array_shift($commandArray);
				$message = implode(" ", $commandArray);
				$this->plugin->discordManager->sendMessage('<' . $sender->getName() . '>' . $message);
				break;

			case "whitelist":
				array_shift($commandArray);
				switch(array_shift($commandArray))
				{

					case "on":
						$this->plugin->getServer()->getNetwork()->setName("現在メンテナンス中 §l§fBattleFront§c2§r §bβ§r");
						break;

					case "off":
						$this->plugin->getServer()->getNetwork()->setName("§l§fBattleFront§c2§r §bβ§r");
						break;

				}
				break;
		}
	}

}
								
