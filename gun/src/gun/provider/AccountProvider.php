<?php

namespace gun\provider;

use pocketmine\IPlayer;
use pocketmine\Player;

use gun\scoreboard\ScoreboardManager;

class AccountProvider extends Provider
{
    /*プロバイダーID*/
    const PROVIDER_ID = "account";
    /*ファイル名(拡張子はなし)*/
    const FILE_NAME = "account";
    /*セーブデータのバージョン*/
    const VERSION = 2;
    /*デフォルトデータ*/
    const DATA_DEFAULT = [];
    /*デフォルトのプレイヤーデータ*/
    const DATA_PLAYER_DAFAULT = [
                                    "exp" => 0,
                                    "kill" => 0,
                                    "death" => 0,
                                    "point" => 0,
                                    "cape" => "",
                                    "setting" => [
                                                "sensitivity" => self::SENSITIVITY_NORMAL,
                                                "auto_heal" => false
                                                ],
                                    "weapon" => [
                                                "main" => [
                                                            "type" => "assaultrifle",
                                                            "id" => "AK-47"
                                                        ],
                                                "sub" => [
                                                            0 => [
                                                                "type" => "handgun",
                                                                "id" => "TT-33"
                                                                ]
                                                        ]
                                                ]
                                ];

    const SENSITIVITY_LOW = -1;
    const SENSITIVITY_NORMAL = 0;
    const SENSITIVITY_HIGH = 1;

    /*ランク関連は別クラスのほうがいいかも…?*/
    const RANKS = [
                    "g-" => [
                                "exp" => 0,
                                "name" => "§8G-§f"
                            ],
                    "g" => [
                                "exp" => 500,
                                "name" => "§8G§f"
                            ],
                    "g+" => [
                                "exp" => 1000,
                                "name" => "§8G+§f"
                            ],
                    "f-" => [
                                "exp" => 2000,
                                "name" => "§8F-§f"
                            ],
                    "f" => [
                                "exp" => 3000,
                                "name" => "§8F§f"
                            ],
                    "f+" => [
                                "exp" => 4000,
                                "name" => "§8F+§f"
                            ],
                    "f++" => [
                                "exp" => 5500,
                                "name" => "§8F++§f"
                            ],
                    "d-" => [
                                "exp" => 8000,
                                "name" => "§7D-§f"
                            ],
                    "d" => [
                                "exp" => 11000,
                                "name" => "§7D§f"
                            ],
                    "d+" => [
                                "exp" => 14000,
                                "name" => "§7D+§f"
                            ],
                    "d++" => [
                                "exp" => 17000,
                                "name" => "§7D+§f"
                            ],
                    "c-" => [
                                "exp" => 20000,
                                "name" => "§eC-§f"
                            ]
                ];

    public function open()
    {
        parent::open();
        foreach (static::DATA_PLAYER_DAFAULT as $key => $value) {
            foreach ($this->data as $name => $data) {
                if(!isset($this->data[$name][$key])) $this->data[$name][$key] = $value;
                if(is_array($value))//雑い
                {
                    foreach ($value as $miniKey => $miniValue) {
                        if(!isset($this->data[$name][$key][$miniKey])) $this->data[$name][$key][$miniKey] = $miniValue;
                    }
                }
            }
        }

        $exps = [];
        foreach ($this->data as $name => $data) {
            if($data["exp"] > 0) $exps[$name] = $data["exp"];
        }
        arsort($exps);

        $count = 0;
        $all = 0;
        foreach ($exps as $key => $value) {
            $count++;
            $all+=$value;
            $this->plugin->getLogger()->info("§6{$count}位§f {$key} : §d{$value}§fEXP");
        }
        $average = round($all / $count);
        $this->plugin->getLogger()->info("§c平均{$average}");
    }

    public function isRegistered(IPlayer $player)
    {
        return isset($this->data[$player->getName()]);
    }

    public function register(IPlayer $player)
    {
        $this->data[$player->getName()] = self::DATA_PLAYER_DAFAULT;
    }

    public function unregister(IPlayer $player)
    {
        unset($this->data[$player->getName()]);
    }

    public function initialize(IPlayer $player)
    {
        $this->register($player);
    }

    public function getExp(IPlayer $player)
    {
        return $this->data[$player->getName()]["exp"];
    }

    public function setExp(IPlayer $player, int $exp)
    {
        $this->data[$player->getName()]["exp"] = $exp;
        if($player->isOnline())
        {
            ScoreboardManager::updateLine($player, ScoreboardManager::LINE_EXP, '§eExp§f : ' . $this->data[$player->getName()]["exp"]);
            ScoreboardManager::updateLine($player, ScoreboardManager::LINE_RANK, '§dRank§f : ' . $this->getRankName($player));
        }
    }

    public function addExp(IPlayer $player, int $exp)
    {
        $this->data[$player->getName()]["exp"] += $exp;
        if($player->isOnline())
        {
            ScoreboardManager::updateLine($player, ScoreboardManager::LINE_EXP, '§eExp§f : ' . $this->data[$player->getName()]["exp"]);
            ScoreboardManager::updateLine($player, ScoreboardManager::LINE_RANK, '§dRank§f : ' . $this->getRankName($player));
        }
    }
    public function getKill(IPlayer $player)
    {
        return $this->data[$player->getName()]["kill"];
    }

    public function setKill(IPlayer $player, int $count)
    {
        $this->data[$player->getName()]["kill"] = $count;
        if($player->isOnline())
        {
            ScoreboardManager::updateLine($player, ScoreboardManager::LINE_KILL, '§cKill§f : ' . $this->data[$player->getName()]["kill"]);
            ScoreboardManager::updateLine($player, ScoreboardManager::LINE_KILLRATIO, '§5K/D§f : ' . $this->getKillRatio($player));
        } 
    }

    public function addKill(IPlayer $player, int $amount)
    {
        $this->data[$player->getName()]["kill"] += $amount;
        if($player->isOnline())
        {
            ScoreboardManager::updateLine($player, ScoreboardManager::LINE_KILL, '§cKill§f : ' . $this->data[$player->getName()]["kill"]);
            ScoreboardManager::updateLine($player, ScoreboardManager::LINE_KILLRATIO, '§5K/D§f : ' . $this->getKillRatio($player));
        } 
    }

    public function getDeath(IPlayer $player)
    {
        return $this->data[$player->getName()]["death"];
    }

    public function setDeath(IPlayer $player, int $count)
    {
        $this->data[$player->getName()]["death"] = $count;
        if($player->isOnline())
        {
            ScoreboardManager::updateLine($player, ScoreboardManager::LINE_DEATH, '§4Death§f : ' . $this->data[$player->getName()]["death"]);
            ScoreboardManager::updateLine($player, ScoreboardManager::LINE_KILLRATIO, '§5K/D§f : ' . $this->getKillRatio($player));
        }  
    }

    public function addDeath(IPlayer $player, int $amount)
    {
        $this->data[$player->getName()]["death"] += $amount;
        if($player->isOnline())
        {
            ScoreboardManager::updateLine($player, ScoreboardManager::LINE_DEATH, '§4Death§f : ' . $this->data[$player->getName()]["death"]);
            ScoreboardManager::updateLine($player, ScoreboardManager::LINE_KILLRATIO, '§5K/D§f : ' . $this->getKillRatio($player));
        }
    }

    public function getKillRatio(IPlayer $player, int $precision = 4)
    {
    	if($this->data[$player->getName()]["death"] === 0){
    	    return 0;
    	}else{
            return round($this->data[$player->getName()]["kill"] / $this->data[$player->getName()]["death"], $precision);
        }
    }

    public function getPoint(IPlayer $player)
    {
        return $this->data[$player->getName()]["point"];
    }

    public function setPoint(IPlayer $player, int $point)
    {
        $this->data[$player->getName()]["point"] = $point;
        ScoreboardManager::updateLine($player, ScoreboardManager::LINE_POINT, '§6Point§f : ' . $this->data[$player->getName()]["point"]);
    }

    public function addPoint(IPlayer $player, int $point)
    {
        $this->data[$player->getName()]["point"] += $point;
        ScoreboardManager::updateLine($player, ScoreboardManager::LINE_POINT, '§6Point§f : ' . $this->data[$player->getName()]["point"]);
    }

    public function subtractPoint(IPlayer $player, int $point)
    {
        $this->data[$player->getName()]["point"] -= $point;
        ScoreboardManager::updateLine($player, ScoreboardManager::LINE_POINT, '§6Point§f : ' . $this->data[$player->getName()]["point"]);
    }

    public function getSetting(IPlayer $player, $key)
    {
        return $this->data[$player->getName()]["setting"][$key];
    }

    public function setSetting(IPlayer $player, $key, $data)
    {
        $this->data[$player->getName()]["setting"][$key] = $data;
    }

    public function getMainWeaponData(IPlayer $player)
    {
        return $this->data[$player->getName()]["weapon"]["main"];
    }

    public function setMainWeaponData(IPlayer $player, $type, $id)
    {
        $this->data[$player->getName()]["weapon"]["main"]["type"] = $type;
        $this->data[$player->getName()]["weapon"]["main"]["id"] = $id;
    }

    public function getSubWeaponData(IPlayer $player, $key)
    {
        $type = null;
        if(isset($this->data[$player->getName()]["weapon"]["sub"][$key])) $type = $this->data[$player->getName()]["weapon"]["sub"][$key];
        return $type;
    }

    public function setSubWeaponData(IPlayer $player, $key, $type, $id)
    {
	    $this->data[$player->getName()]["weapon"]["sub"][$key]["type"] = $type;
        $this->data[$player->getName()]["weapon"]["sub"][$key]["id"] = $id;
    }

    public function getCapeId(IPlayer $player)
    {
        return $this->data[$player->getName()]["cape"];
    }

    public function setCapeId(IPlayer $player, $id)
    {
        $this->data[$player->getName()]["cape"] = $id;
    }

    public function getRankName(IPlayer $player)
    {
        $name = $player->getName();
        $rank = "";
        foreach (self::RANKS as $key => $value) {
            if($value["exp"] > $this->data[$name]["exp"]) break;
            $rank = $value["name"];
        }

        return $rank;
    }

}