<?php
namespace gun\form\forms;

use gun\job\JobManager;
use gun\provider\JobProvider;

class JobShopForm extends Form {
	
	public function send(int $id){
		$cache = [];
		switch($id){
			case 1:
				$buttons = [];
				$jobIDs = JobManager::getAllId();
				foreach($jobIDs as $jid){
					$object = JobManager::getObject($jid);
					$buttons[] = [
						"text" => "§l" . $object->getName() . "§r§8\n" . $object->getDescription()
					];
					$cache[] = 2;
				}
				$data = [
					'type' => 'form',
					'title' => '§lJobShopForm(職業選択)',
					'content' => '就きたいJobを選んでください',
					'buttons' => $buttons
				];
				break;
			case 2:
				$jobID = JobManager::getAllId()[$this->lastData];
				$this->jobID = $jobID;
				$object = JobManager::getObject($jobID);
				$content = $object->getName()."に転職しますか？\n";
				$content .= "\n§b特徴 : ".$object->getDescription();
				$this->sendModal("§lJobShopForm(職業選択)", $content, "転職", "戻る", 3, 1);
				return true;
				break;
			case 3:
				JobProvider::get()->setJob($this->player, $this->jobID);
				$this->sendModal('§lJobShopForm(職業選択)', "転職が完了しました", "続ける", "戻る", 1, 0);
				return true;
				break;
		}
		
		if($cache !== []){
			$this->lastSendData = $data;
			$this->cache = $cache;
			$this->show($id, $data);
		}
	}
}
			
					
				
