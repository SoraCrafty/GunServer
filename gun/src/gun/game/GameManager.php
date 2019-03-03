<?php

namespace gun\game;

use gun\provider\MainSettingProvider;

use gun\game\games\TeamDeathMatch;

class GameManager
{

	private static $gameObject;

	public static function init($plugin)
	{
		switch(MainSettingProvider::get()->getGameMode())
		{
			
			case TeamDeathMatch::GAME_ID:
				self::$gameObject = new TeamDeathMatch($plugin);
				break;

			default:
				$plugin->getServer()->getLogger()->warning("ゲームIDが不正です");
				break;
		}

		$plugin->getServer()->getPluginManager()->registerEvents(new GameListener(self::$gameObject), $plugin);
	}

	public static function getObject()
	{
		return self::$gameObject;
	}

}
