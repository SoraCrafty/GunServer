<?php

namespace gun\bossbar;

use pocketmine\Player;
use pocketmine\Server;

use pocketmine\math\Vector3;

use pocketmine\entity\Entity;

use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\network\mcpe\protocol\BossEventPacket;
use pocketmine\network\mcpe\protocol\RemoveEntityPacket;
use pocketmine\network\mcpe\protocol\SetEntityDataPacket;
use pocketmine\network\mcpe\protocol\UpdateAttributesPacket;

class BossBar{

	/*Mainクラスのオブジェクト*/
	private $plugin;
	/*用いるEid*/
	private $eid;
	/*タイトル*/
	private $title = "";
	/*ゲージの割合*/
	private $percentage = 0;
	/*ボスバーが表示状態かどうか*/
	private $visible = false;

	public function __construct($plugin)
	{
		$this->plugin = $plugin;
		$this->eid = Entity::$entityCount++;
	}

	public function showBossBar($player)
	{
		$apk = new AddEntityPacket();
		$apk->entityRuntimeId = $this->eid;
		$apk->type = 37;
		$apk->position = $player->getPosition();
		$apk->metadata = [
			Entity::DATA_LEAD_HOLDER_EID => [
								Entity::DATA_TYPE_LONG, -1
								],
			Entity::DATA_FLAGS => [
								Entity::DATA_TYPE_LONG, 0 ^ 1 << Entity::DATA_FLAG_SILENT ^ 1 << Entity::DATA_FLAG_INVISIBLE ^ 1 << Entity::DATA_FLAG_NO_AI
								],
			Entity::DATA_SCALE => [
								Entity::DATA_TYPE_FLOAT, 0
								],
			Entity::DATA_NAMETAG => [
								Entity::DATA_TYPE_STRING, $this->title
								],
			Entity::DATA_BOUNDING_BOX_WIDTH => [
								Entity::DATA_TYPE_FLOAT, 0
								],
			Entity::DATA_BOUNDING_BOX_HEIGHT => [
								Entity::DATA_TYPE_FLOAT, 0
								]
							];
		$player->dataPacket($apk);

		$bpk = new BossEventPacket();
		$bpk->bossEid = $this->eid;
		$bpk->eventType = BossEventPacket::TYPE_SHOW;
		$bpk->title = $this->title;
		$bpk->healthPercent = $this->percentage;
		$bpk->unknownShort = 0;
		$bpk->color = 0;
		$bpk->overlay = 0;
		$bpk->playerEid = 0;
		$player->dataPacket($bpk);
	}

	public function updatePercentage($player)
	{
		$upk = new UpdateAttributesPacket();
		$upk->entries[] = new BossBarValues(1, 600, max(1, min([$this->percentage * 100, 100])) / 100 * 600, 'minecraft:health');
		$upk->entityRuntimeId = $this->eid;
		$player->dataPacket($upk);

		$bpk = new BossEventPacket();
		$bpk->bossEid = $this->eid;
		$bpk->eventType = BossEventPacket::TYPE_SHOW;
		$bpk->title = $this->title;
		$bpk->healthPercent = $this->percentage;
		$bpk->unknownShort = 0;
		$bpk->color = 0;
		$bpk->overlay = 0;
		$bpk->playerEid = 0;
		$player->dataPacket($bpk);
	}

	public function updateTitle($player)
	{
		$spk = new SetEntityDataPacket();
		$spk->metadata = [
						Entity::DATA_NAMETAG => [
							Entity::DATA_TYPE_STRING, $this->title
						]
					];
		$spk->entityRuntimeId = $this->eid;
		$player->dataPacket($spk);

		$bpk = new BossEventPacket();
		$bpk->bossEid = $this->eid;
		$bpk->eventType = BossEventPacket::TYPE_SHOW;
		$bpk->title = $this->title;
		$bpk->healthPercent = $this->percentage;
		$bpk->unknownShort = 0;
		$bpk->color = 0;
		$bpk->overlay = 0;
		$bpk->playerEid = 0;
		$player->dataPacket($bpk);
	}

	/*private function setPercentage($percentage)//0~100
	{
		$this->percentage = $percentage / 100;

		$upk = new UpdateAttributesPacket();
		$upk->entries[] = new BossBarValues(1, 600, max(1, min([$this->percentage, 100])) / 100 * 600, 'minecraft:health');
		$upk->entityRuntimeId = $this->eid;
		$player->dataPacket($upk);

		$bpk = new BossEventPacket();
		$bpk->bossEid = $this->eid;
		$bpk->eventType = BossEventPacket::TYPE_SHOW;
		$bpk->title = $this->title;
		$bpk->healthPercent = $this->percentage;
		$bpk->unknownShort = 0;
		$bpk->color = 0;
		$bpk->overlay = 0;
		$bpk->playerEid = 0;
		Server::getInstance()->broadcastPacket($players, $bpk);
	}*/

	/*const ENTITY = 37;

	public static function sendBossBar($players, Vector3 $vector, int $eid, string $title, $color = 0){
		self::removeBossBar($players, $eid);

		$packet = new AddEntityPacket();
		$packet->entityRuntimeId = $eid;
		$packet->type = self::ENTITY;
		$packet->position = $vector;
		$packet->metadata = [Entity::DATA_LEAD_HOLDER_EID => [Entity::DATA_TYPE_LONG, -1], Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, 0 ^ 1 << Entity::DATA_FLAG_SILENT ^ 1 << Entity::DATA_FLAG_INVISIBLE ^ 1 << Entity::DATA_FLAG_NO_AI], Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 0],
			Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $title], Entity::DATA_BOUNDING_BOX_WIDTH => [Entity::DATA_TYPE_FLOAT, 0], Entity::DATA_BOUNDING_BOX_HEIGHT => [Entity::DATA_TYPE_FLOAT, 0]];
		Server::getInstance()->broadcastPacket($players, $packet);

		$bpk = new BossEventPacket();
		$bpk->bossEid = $eid;
		$bpk->eventType = BossEventPacket::TYPE_SHOW;
		$bpk->title = $title;
		$bpk->healthPercent = 1;
		$bpk->unknownShort = 0;
		$bpk->color = $color;
		$bpk->overlay = 0;
		$bpk->playerEid = 0;
		Server::getInstance()->broadcastPacket($players, $bpk);
	}

	public static function setPercentage(int $percentage, int $eid, $players = []){
		if (empty($players)) $players = Server::getInstance()->getOnlinePlayers();
		if (!count($players) > 0) return;

		$upk = new UpdateAttributesPacket();
		$upk->entries[] = new BossBarValues(1, 600, max(1, min([$percentage, 100])) / 100 * 600, 'minecraft:health');
		$upk->entityRuntimeId = $eid;
		Server::getInstance()->broadcastPacket($players, $upk);

		$bpk = new BossEventPacket();
		$bpk->bossEid = $eid;
		$bpk->eventType = BossEventPacket::TYPE_SHOW;
		$bpk->title = "";
		$bpk->healthPercent = $percentage / 100;
		$bpk->unknownShort = 0;
		$bpk->color = 0;
		$bpk->overlay = 0;
		$bpk->playerEid = 0;
		Server::getInstance()->broadcastPacket($players, $bpk);
	}

	public static function setTitle(string $title, int $eid, $players = []){
		if (!count(Server::getInstance()->getOnlinePlayers()) > 0) return;

		$npk = new SetEntityDataPacket();
		$npk->metadata = [Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $title]];
		$npk->entityRuntimeId = $eid;
		Server::getInstance()->broadcastPacket($players, $npk);

		$bpk = new BossEventPacket();
		$bpk->bossEid = $eid;
		$bpk->eventType = BossEventPacket::TYPE_SHOW;
		$bpk->title = $title;
		$bpk->healthPercent = 1;
		$bpk->unknownShort = 0;
		$bpk->color = 0;
		$bpk->overlay = 0;
		$bpk->playerEid = 0;
		Server::getInstance()->broadcastPacket($players, $bpk);
	}

	public static function removeBossBar($players, int $eid){
		if (empty($players)) return false;

		$pk = new RemoveEntityPacket();
		$pk->entityUniqueId = $eid;
		Server::getInstance()->broadcastPacket($players, $pk);
		return true;
	}*/
}