<?php

namespace gun\provider;

use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;

class RankingProvider extends Provider
{

    const PROVIDER_ID = "ranking";
    /*ファイル名(拡張子はなし)*/
    const FILE_NAME = "ranking";
    /*セーブデータのバージョン*/
    const VERSION = 1;
    /*デフォルトデータ*/
    const DATA_DEFAULT = [
                            "x" => 0,
                            "y" => 0,
                            "z" => 0
    					];

    public function open()
    {
        parent::open();
    }

    public function setPosition($vector)
    {
        $this->data["x"] = $vector->x;
        $this->data["y"] = $vector->y;
        $this->data["z"] = $vector->z;
    }

    public function getPosition()
    {
        return $this->data;
    }

}
