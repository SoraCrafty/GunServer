<?php

namespace gun\provider;

use pocketmine\IPlayer;

class AccountProvider extends Provider
{
    /*プロバイダーID*/
    const PROVIDER_ID = "account";
    /*ファイル名(拡張子はなし)*/
    const FILE_NAME = "account";
    /*セーブデータのバージョン*/
    const VERSION = 1;
    /*デフォルトデータ*/
    const DATA_DEFAULT = [];
    /*デフォルトのプレイヤーデータ*/
    const DATA_PLAYER_DAFAULT = [
                                    "exp" => 0,
                                    "kill" => 0,
                                    "death" => 0,
                                    "point" => 0,
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
    }

    public function addExp(IPlayer $player, int $exp)
    {
        $this->data[$player->getName()]["exp"] += $exp;
    }

    public function getKill(IPlayer $player)
    {
        return $this->data[$player->getName()]["kill"];
    }

    public function setKill(IPlayer $player, int $count)
    {
        $this->data[$player->getName()]["kill"] = $count;
    }

    public function addKill(IPlayer $player, int $amount)
    {
        $this->data[$player->getName()]["kill"] += $amount;
    }

    public function getDeath(IPlayer $player)
    {
        return $this->data[$player->getName()]["death"];
    }

    public function setDeath(IPlayer $player, int $count)
    {
        $this->data[$player->getName()]["death"] = $count;
    }

    public function addDeath(IPlayer $player, int $amount)
    {
        $this->data[$player->getName()]["death"] += $amount;
    }

    public function getKillRatio(IPlayer $player, int $precision = 2)
    {
        return round($this->data[$player->getName()]["kill"] / $this->data[$player->getName()]["death"], $precision);
    }

    public function getPoint(IPlayer $player)
    {
        return $this->data[$player->getName()]["point"];
    }

    public function setPoint(IPlayer $player, int $point)
    {
        $this->data[$player->getName()]["point"] = $point;
    }

    public function addPoint(IPlayer $player, int $point)
    {
        $this->data[$player->getName()]["point"] += $point;
    }

    public function subtractPoint(IPlayer $player, int $point)
    {
        $this->data[$player->getName()]["point"] -= $point;
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

}