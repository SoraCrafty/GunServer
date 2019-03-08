<?php

namespace gun\provider;

use pocketmine\utils\Config;

use gun\weapons\AssaultRifle;
use gun\weapons\SniperRifle;
use gun\weapons\ShotGun;

class MainWeaponShop extends Provider
{

    const PROVIDER_ID = "mwshop";
    /*ファイル名(拡張子はなし)*/
    const FILE_NAME = "shop_main";
    /*セーブデータのバージョン*/
    const VERSION = 1;
    /*デフォルトデータ*/
    const DATA_DEFAULT = [
                        AssaultRifle::WEAPON_ID => [
                        	"AK-47" => 500
                                        ],
                        SniperRifle::WEAPON_ID => [
                            "Kar98k" => 500
                                        ],
                        ShotGun::WEAPON_ID => [
                            "Remington870" => 500
                                        ]
                        ];

    public function getItems($type)
    {
        $items = null;
        if(isset($this->data[$type])) $items = $this->data[$type];
        return $items;
    }

    public function deleteItem($type, $id)
    {
        unset($this->data[$type][$id]);
    }

    public function getPrice($type, $id)
    {
        $price = null;
        if(isset($this->data[$type][$id])) $price = $this->data[$type][$id];
        return $price;
    }

    public function setPrice($type, $id, $price)
    {
        $this->data[$type][$id] = $price;
    }

}
























