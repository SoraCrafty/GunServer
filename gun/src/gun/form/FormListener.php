<?php

namespace gun\form;

use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\player\PlayerQuitEvent;

use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ServerSettingsRequestPacket;

use gun\form\forms\ServerSettingForm;

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
		elseif($pk instanceof ServerSettingsRequestPacket)
		{
			FormManager::register(new ServerSettingForm($this->plugin, $event->getPlayer()));
		}
	}

	public function onQuit(PlayerQuitEvent $event)
	{
		$form = FormManager::getForm($event->getPlayer());
		if(!is_null($form)) $form->close();
	}
}

