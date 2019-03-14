<?php

namespace gun\game\games;

abstract class Game 
{
    const GAME_ID = "";
    const GAME_NAME = "";

    public static function getId()
    {
        return static::GAME_ID;
    }

    public static function getName()
    {
        return static::GAME_NAME;
    }

    public function isGaming()
    {

        return false;
    }

    public function onInteract($event)
    {

    }

    public function onEventNPCTouch($event)
    {

    }

    public function onPlayerDeath($event)
    {

    }

    public function onDamage($event)
    {

    }

    public function onRespawn($event)
    {

    }

    public function onQuit($event)
    {
        
    }

    public function onArmorChange($event)
    {

    }

}