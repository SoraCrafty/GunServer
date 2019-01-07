<?php

namespace gun\events;

class PlayerDropItemEvent extends Events{

	public function call($event){
		$event->setCancelled();

	}


}