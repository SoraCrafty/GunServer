<?php

namespace gun\events;

class PlayerChatEvent extends Events{

	public function call($event){
		$message = '<' . $event->getPlayer()->getName() . '>' . $event->getMessage();
		$this->plugin->discordManager->sendMessage($message);
	}

}