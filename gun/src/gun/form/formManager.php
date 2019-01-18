<?php

namespace gun\form;

use pocketmine\event\Listener;

use pocketmine\event\server\DataPacketReceiveEvent;

use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;

class FormManager implements Listener
{
	/*Mainクラスのオブジェクト*/
	private $plugin;

	public function __construct($plugin)
	{
		$this->plugin = $plugin;//いらんかも
	}
}
		
