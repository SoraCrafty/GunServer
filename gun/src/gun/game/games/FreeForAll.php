<?php

namespace gun\game\games;

abstract class Game 
{
    const GAME_ID = "ffa";
    const GAME_NAME = "FFA";

    public static function getId()
    {
        return static::GAME_ID;
    }

    public function getName()
    {
        return static::GAME_NAME;
    }

    public function isGaming()
    {
        return true;
    }


    public function join($player)
    {

    }

    public function leave($player)
    {

    }

    public function onEventNPCTouch($event)
    {
        if($event->getEventId() !== "game") return true;
    }

    public function onPlayerDeath($event)
    {

    }

    public function onDamage($event)
    {

    }


}