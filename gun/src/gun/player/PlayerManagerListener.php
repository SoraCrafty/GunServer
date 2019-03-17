<?php

namespace gun\player;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerChangeSkinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;

use pocketmine\network\mcpe\protocol\LoginPacket;

class PlayerManagerListener implements Listener
{
	/*Mainクラスのオブジェクト*/
	private $plugin;
	/*PlayerManagerのオブジェクト*/
	private $manager;

	public function __construct($plugin, $manager)
	{
		$this->plugin = $plugin;
		$this->manager = $manager;
	}

	public function onJoin(PlayerJoinEvent $event)
	{

	}

	public function onChangeSkin(PlayerChangeSkinEvent $event)
	{

	}

	public function onQuit(PlayerQuitEvent $event)
	{
		$this->manager->unsetData($event->getPlayer());
	}

	public function onDataPacketReceive(DataPacketReceiveEvent $event)
	{
		$pk = $event->getPacket();
		if($pk instanceof LoginPacket)
		{
			$this->manager->setOS($pk->username, $pk->clientData["DeviceOS"]);
		}
	}
}

