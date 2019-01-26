<?php

namespace gun\form;

use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\player\PlayerJumpEvent;

use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;

use gun\form\forms\TestForm;

class FormListener implements Listener
{
	/*Mainクラスのオブジェクト*/
	public $plugin;

	public function __construct($plugin)
	{
		$this->plugin = $plugin;
	}

	public function onPacketReceive(DataPacketReceiveEvent $event)
	{
		$pk = $event->getPacket();
		if($pk instanceof ModalFormResponsePacket)
		{
			$form = FormManager::getForm($event->getPlayer());
			if(!is_null($form)) $form->response($pk->formId, json_decode($pk->formData, true));
		}
	}
}

