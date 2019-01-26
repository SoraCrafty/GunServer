<?php

namespace gun\provider;

use pocketmine\utils\Config;

class MainWeaponShop extends Provider
{

    const PROVIDER_ID = "account";
    /*ファイル名(拡張子はなし)*/
    const FILE_NAME = "shop_main";
    /*セーブデータのバージョン*/
    const VERSION = 1;
    /*デフォルトデータ*/
    const DATA_DEFAULT = [];

    public function getItems($type)
    {
        $items = null;
        if(isset($this->data[$type])) $items = $this->data[$type];
        return $items;
    }

    /*public function getPrice($type, $id)
    {
        $price = null;
        if(isset($this->data[$type][$id])) $price = $this->data[$type][$id];
        return $price;
    }

    public function setPrice($type, $id, $value)
    {
        $this->data[$type][$id] = $value;
    }*/

}
























