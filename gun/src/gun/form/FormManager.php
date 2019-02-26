<?php

namespace gun\form;

use pocketmine\Player;

use gun\form\forms\Form;

class FormManager
{
	/*Weaponの配列*/
	private static $forms = [];

	public static function init($plugin)
	{
		$plugin->getServer()->getPluginManager()->registerEvents(new FormListener($plugin), $plugin);
	}

	public static function register(Form $form)
	{
		self::$forms[$form->getPlayer()->getName()] = $form;
	}

	public static function close(Form $form)
	{
		unset(self::$forms[$form->getPlayer()->getName()]);
	}

	public static function getForm(Player $player)
	{
		$form = null;
		$name = $player->getName();
		if(isset(self::$forms[$name])) $form = self::$forms[$name];
		return $form;
	}
}
		
