<?php

namespace gun\form\forms;

class GameSettingForm extends Form
{

	public function send(int $id)
	{
		$cache = [];
		switch($id)
		{
			case 1://メイン画面
				$buttons = [
						[
							"text" => "§lチームデスマッチ -TeamDeathMatch-§r§8\nチームに分かれてキル数"
						]
						];
				$cache = [11];
				$data = [
					'type'    => "form",
					'title'   => "§lゲーム設定画面",
					'content' => "編集したいゲームを設定してください",
					'buttons' => $buttons
				];
				break;

			/*11~20 : TDM*/
			case 11:
				
				break;
		}

		if($cache !== []){
			$this->lastSendData = $data;
			$this->cache = $cache;
			$this->show($id, $data);
		}
	}

}