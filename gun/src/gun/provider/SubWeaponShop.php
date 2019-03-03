<?php

namespace gun\provider;

use pocketmine\utils\Config;

use gun\weapons\HandGun;

class SubWeaponShop extends MainWeaponShop
{

    const PROVIDER_ID = "swshop";
    /*ファイル名(拡張子はなし)*/
    const FILE_NAME = "shop_sub";
    /*セーブデータのバージョン*/
    const VERSION = 1;
    /*デフォルトデータ*/
    const DATA_DEFAULT = [
                        HandGun::WEAPON_ID => [
                        	"TT-33" => 0
                                        ]
                        ];

}
























