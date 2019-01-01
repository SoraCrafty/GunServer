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

	public function show()
	{
		foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
			$this->showBossBar($player);
		}
	}

	public function hide()
	{
		foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
			$this->hideBossBar($player);
		}
	}

	public function setTitle($title)
	{
		$this->title = $title;
		foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
			$this->updateTitle($player);
		}
	}

	public function setPercentage($percentage)
	{
		$this->percentage = $percentage;
		foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
			$this->updatePercentage($player);
		}
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

	public function hideBossBar($player)
	{
		$rpk = new RemoveEntityPacket();
		$rpk->entityUniqueId = $this->eid;
		$player->dataPacket($rpk);
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
}