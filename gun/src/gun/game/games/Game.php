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

    public function getName()
    {
        return static::GAME_NAME;
    }

    public function isGaming()
    {

        return false;
    }

    public function apply($player)
    {

    }

    public function unapply($player)
    {

    }

    public function join($player)
    {

    }

    public function leave($player)
    {

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


}