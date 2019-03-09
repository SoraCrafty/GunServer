<?php

namespace gun\events;

use pocketmine\item\Item;
use pocketmine\Player;

use gun\form\FormManager;
use gun\form\forms\TestFireForm;

class EventNPCTouchEvent extends Events{

	public function call($event){
        switch($event->getEventId())
        {

            case "testfire":
            	FormManager::register(new TestFireForm($this->plugin, $event->getPlayer()));
                break;

        }
	}

}