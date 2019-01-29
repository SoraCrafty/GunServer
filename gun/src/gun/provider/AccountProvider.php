<?php

namespace gun\provider;

use pocketmine\utils\Config;

class AccountProvider
{

    const PROVIDER_ID = "";
    /*ファイル名(拡張子はなし)*/
    const FILE_NAME = "";
    /*セーブデータのバージョン*/
    const VERSION = 1;
    /*デフォルトデータ*/
    const DATA_DEFAULT = [];

    /*Mainクラスのオブジェクト*/
    protected $plugin;
    /*使用中のセーブデータのバージョン*/
    protected $version;
    /*セーブデータ*/
    protected $data;

    public function __construct($plugin)
    {
        $this->plugin = $plugin;
        $this->open();
    }

    public function open()
    {
        $this->config = new Config($this->plugin->getDataFolder() . static::FILE_NAME . ".yml", Config::YAML, ["version" => static::VERSION, "data" => static::DATA_DEFAULT]);
        $this->version = $this->config->get("version");
        $this->data = $this->config->get("data");
    }

    public function save()
    {
        $this->config->set("data", $this->data);
        $this->config->save();
    }

    public function close()
    {
        $this->save();
    }

    public function getId()
    {
        return static::PROVIDER_ID;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function getAllData()
    {
        return $this->data;
    }

}
























