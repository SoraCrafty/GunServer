<?php

namespace gun\provider;

use pocketmine\level\Position;

class TestFiringFieldProvider extends Provider
{

    const PROVIDER_ID = "test_firing_field";
    /*ファイル名(拡張子はなし)*/
    const FILE_NAME = "firing_field";
    /*セーブデータのバージョン*/
    const VERSION = 1;
    /*デフォルトデータ*/
    const DATA_DEFAULT = [
    						"world" => "",
                            "position" => [
                                        "x" => 0,
                                        "y" => 0,
                                        "z" => 0
                                        ]
    					];

    public function open()
    {
        parent::open();
        if($this->data["world"] === "") $this->data["world"] = $this->plugin->getServer()->getDefaultLevel()->getFolderName();
    }

    public function getPosition()
    {
        return new Position($this->data["position"]["x"], $this->data["position"]["y"], $this->data["position"]["z"], $this->plugin->getServer()->getLevelByName($this->data["world"]));
    }

    public function setPosition(Position $position)
    {
        $this->data = [
                        "world" => $position->getLevel()->getFolderName(),
                        "position" => [
                                    "x" => $position->getX(),
                                    "y" => $position->getY(),
                                    "z" => $position->getZ()
                                    ]
                    ];
    }

}
























