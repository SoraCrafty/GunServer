<?php

namespace gun\fireworks;

use pocketmine\math\Vector3;
use pocketmine\entity\Entity;
use pocketmine\level\Position;

use gun\fireworks\item\Fireworks;
use gun\fireworks\entity\FireworksRocket;

class FireworksAPI
{
	/*Mainクラスのオブジェクト*/
	private $plugin;

	public function __construct($plugin)
	{
		$this->plugin = $plugin;
	}

	public function spawn(Position $position,int $flightDuration, int $type, string $color, string $fade = "", int $flicker = 0, int $trail = 0)
	{
		$fireworks = new Fireworks();
		$fireworks->setFlightDuration($flightDuration);
		$fireworks->addExplosion($type, $color, $fade = "", $flicker = 0, $trail = 0);

		$nbt = Entity::createBaseNBT($position, new Vector3(0.001, 0.05, 0.001), lcg_value() * 360, 90);
		$entity = Entity::createEntity("FireworksRocket", $position->getLevel(), $nbt, $fireworks);
		$entity->spawnToAll();
	}
}