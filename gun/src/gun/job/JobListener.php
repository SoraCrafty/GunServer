<?php

namespace gun\job;

use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerMoveEvent;

use gun\provider\JobProvider;

class JobListener implements Listener {

	private $plugin;
	
	public function __construct($plugin){
		$this->plugin = $plugin;
	}
	
	public function onEvent($eventname, $player, $event){
		$job = JobProvider::get()->getJob($player);
		if(is_null($job)) return false;
		$object = JobManager::getObject($job);
		$object->$eventname($player, $event);
	}
	
	public function onInteract(PlayerInteractEvent $event){
		$player = $event->getPlayer();
		$this->onEvent(Job::EVENT_INTERACT, $player, $event);
	}
	
	public function onSneak(PlayerToggleSneakEvent $event){
		$this->onEvent(Job::EVENT_SNEAK, $event->getPlayer(), $event);
	}
	
	public function onMove(PlayerMoveEvent $event){
		$this->onEvent(Job::EVENT_MOVE, $event->getPlayer(), $event);
	}
	
	public function onDeath(PlayerDeathEvent $event){
		$player = $event->getPlayer();
		$lastDamageCause = $player->getLastDamageCause();
		if(!isset($lastDamageCause)) return false;
		if($lastDamageCause instanceof EntityDamageByEntityEvent){
			$this->onEvent(Job::EVENT_KILL, $lastDamageCause->getDamager(), $lastDamageCause);
		}
		$this->onEvent(Job::EVENT_DEATH, $player, $event);
	}
}
	
