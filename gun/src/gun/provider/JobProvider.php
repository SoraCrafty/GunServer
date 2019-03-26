<?php
namespace gun\provider;

use pocketmine\IPlayer;
use pocketmine\Player;

class JobProvider extends Provider {
	
	/*プロバイダーID*/
	const PROVIDER_ID = "job";
	/*ファイル名*/
	const FILE_NAME = "job";
	/*セーブデータのバージョっb*/
	const VERSION = 1;
	/*デフォルトデータ*/
	const DATA_DEFAULT = [];
	/*デフォルトのjobId*/
	const DATA_JOB_DEFAULT = array('job' => 'ranger');
	
	public function hasJob(IPlayer $player){
		return isset($this->data[$player->getName()]);
	}
	
	public function register(IPlayer $player){
		$this->data[$player->getName()] = self::DATA_JOB_DEFAULT;
	}
	
	public function getJob(IPlayer $player){
		if($this->hasJob($player)){
			return $this->data[$player->getName()];
		}
		return null;
	}
	
	public function setJob(IPlayer $player, $jobID){
		$this->data[$player->getName()] = $jobID;
	}
}
