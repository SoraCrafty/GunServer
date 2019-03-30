<?php

namespace gun\provider;

use pocketmine\utils\Config;
use pocketmine\utils\Color;
use pocketmine\level\Position;

class FlagSettingProvider extends Provider
{

    const PROVIDER_ID = "flagsetting";
    /*ファイル名(拡張子はなし)*/
    const FILE_NAME = "flagsetting";
    /*セーブデータのバージョン*/
    const VERSION = 1;
    /*デフォルトデータ*/
    const DATA_DEFAULT = [];
    /*デフォルトのゲームデータ*/
    const DATA_GAME_DEFAULT = [
                            "Stage_Name" => "Default",
    						"Game_Time" => 15 * 60,
    						"Waiting_Time" => 60,
    						"Flagcount_Max" => 15,
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
        if($this->data === []) $this->data[$this->plugin->getServer()->getDefaultLevel()->getFolderName()] = self::DATA_GAME_DEFAULT;
    }

    public function getRandmonLevelName()
    {
        return array_rand($this->data);
    }

    public function getGameTime($key)
    {
    	return $this->data[$key]["Game_Time"];
    }

    public function getWaitingTime($key)
    {
    	return $this->data[$key]["Waiting_Time"];
    }

    public function getMaxFlagCount($key)
    {
    	return $this->data[$key]["Flagcount_Max"];
    }

    public function getHealth($key)
    {
        return $this->data[$key]["Player_Health"];
    }

    public function getTeamName($key, $id)
    {
    	return $this->data[$key]["Team_Data"][$id]["name"];
    }

    public function getTeamNameDecoration($key, $id)
    {
    	return $this->data[$key]["Team_Data"][$id]["decoration"];
    }

    public function getTeamSpawn($key, $id)
    {
    	return new Position($this->data[$key]["Team_Data"][$id]["spawn"]["x"], $this->data[$key]["Team_Data"][$id]["spawn"]["y"], $this->data[$key]["Team_Data"][$id]["spawn"]["z"], $this->plugin->getServer()->getLevelByName($key));
    }

    public function getTeamColor($key, $id)
    {
        return new Color($this->data[$key]["Team_Data"][$id]["color"]["r"], $this->data[$key]["Team_Data"][$id]["color"]["g"], $this->data[$key]["Team_Data"][$id]["color"]["b"]);
    }

    public function getStageName($key)
    {
        return $this->data[$key]["Stage_Name"];
    }

    public function setStageData($key, $data)
    {
        $this->data[$key] = $data;
    }

    public function getStageData($key)
    {
        $data = null;
        if(isset($this->data[$key]))
        {
            $data = $this->data[$key];
        }
        return $data;
    }

    public function unsetStageData($key)
    {
        unset($this->data[$key]);
    }

}

