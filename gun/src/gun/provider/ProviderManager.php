<?php

namespace gun\provider;

use pocketmine\utils\Config;

class ProviderManager
{

    private static $providers = [];

    public static function init($plugin)
    {
        self::register(new GameSettingProvider($plugin));
        self::register(new MainWeaponShop($plugin));
    }

    public static function register(Provider $provider)
    {
        self::$providers[$provider->getId()] = $provider;
    }

    public static function close()
    {
        foreach (self::$providers as $provider) {
            $provider->close();
        }
    }

    public static function get($id)
    {
        $provider = null;
        if(isset(self::$providers[$id])) $provider = self::$providers[$id];
        return $provider;
    }

}
























