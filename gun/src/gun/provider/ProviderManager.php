<?php

namespace gun\provider;

use pocketmine\utils\Config;

class ProviderManager
{

    private static $providers = [];

    public static function init($plugin)
    {
        self::register(new TDMSettingProvider($plugin));
        self::register(new MainSettingProvider($plugin));
        self::register(new MainWeaponShop($plugin));
        self::register(new SubWeaponShop($plugin));
        self::register(new DiscordProvider($plugin));
        self::register(new AccountProvider($plugin));
        self::register(new GuideBookProvider($plugin));
        self::register(new RankingProvider($plugin));
        self::register(new TestFiringFieldProvider($plugin));
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
























