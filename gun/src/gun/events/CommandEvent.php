<?php

namespace gun\events;

use pocketmine\Player;

class CommandEvent extends Events{

	public function call($event)
	{
		$sender = $event->getSender();
		if(!$sender instanceof Player)
		{
			$command = $event->getCommand();
			$commandArray = explode(" ", $command);
			if($commandArray[0] === "say")
			{
				array_shift($commandArray);
				$message = implode(" ", $commandArray);
				$this->plugin->discordManager->sendMessage('<' . $sender->getName() . '>' . $message);
			}
		}
	}

}
								
