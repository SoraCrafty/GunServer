<?php

namespace gun\provider;

use pocketmine\utils\Config;
use pocketmine\utils\Color;

class TDMSettingProvider extends Provider
{

    const PROVIDER_ID = "tdmsetting";
    /*ファイル名(拡張子はなし)*/
    const FILE_NAME = "tdmsetting";
    /*セーブデータのバージョン*/
    const VERSION = 1;
    /*デフォルトデータ*/
    const DATA_DEFAULT = [
    						"Game_Time" => 15 * 60,
    						"Waiting_Time" => 60,
    						"Killcount_Max" => 50,
                            "Player_Health" => 40,
    						"Team_Data" => [
				    				0 => [
				                        "name" => "Red",
				                        "decoration" => "§c",
				                        "spawn" => [
				                        			"x" => 0,
				                        			"y" => 5,
				                        			"z" => 0
				                        		],
                                        "color" => [
                                                    "r" => 255,
                                                    "g" => 0,
                                                    "b" => 0
                                                ]
				                        ],
				                    1 => [
				                        "name" => "Blue",
				                        "decoration" => "§b",
				                        "spawn" => [
				                        			"x" => 0,
				                        			"y" => 5,
				                        			"z" => 0
				                        		],
                                        "color" => [
                                                    "r" => 0,
                                                    "g" => 0,
                                                    "b" => 255
                                                ]
				                       	]
				                    ]
    					];

    public function open()
    {
        parent::open();
        if(!isset($this->data["Team_Data"][0]["color"])) $this->data["Team_Data"][0]["color"] = self::DATA_DEFAULT["Team_Data"][0]["color"];
        if(!isset($this->data["Team_Data"][1]["color"])) $this->data["Team_Data"][1]["color"] = self::DATA_DEFAULT["Team_Data"][1]["color"];
    }

    public function getGameTime()
    {
    	return $this->data["Game_Time"];
    }

    public function getWaitingTime()
    {
    	return $this->data["Waiting_Time"];
    }

    public function getMaxKillCount()
    {
    	return $this->data["Killcount_Max"];
    }

    public function getHealth()
    {
        return $this->data["Player_Health"];
    }

    public function getTeamName($id)
    {
    	return $this->data["Team_Data"][$id]["name"];
    }

    public function getTeamNameDecoration($id)
    {
    	return $this->data["Team_Data"][$id]["decoration"];
    }

    public function getTeamSpawn($id)
    {
    	return $this->data["Team_Data"][$id]["spawn"];
    }

    public function getTeamColor($id)
    {
        return new Color($this->data["Team_Data"][$id]["color"]["r"], $this->data["Team_Data"][$id]["color"]["g"], $this->data["Team_Data"][$id]["color"]["b"]);
    }

}
























