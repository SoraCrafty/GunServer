<?php

namespace gun\form\forms;

use gun\form\FormManager;

use gun\provider\ProviderManager;
use gun\provider\AccountProvider;

class StatusForm extends Form
{
	public function send(int $id)
	{
		$cache = [];
		switch($id)
		{
			case 1:
			$buttons = [
				[
					"text" => "ステータスを確認"
			],
			[
				"text" => "設定画面"
			],
			[
				"text" => "検索",
			]
		];

		$data = [
			'type' => "form",
			'title' => "§lインフォメーション / Information",
			'content' => "自分の情報や他人の情報を確認することができます。また、自分のステータスの変更も行うことが出来ます",
			'buttons' => $buttons,
		];
		$cache = [3,2,4];
		break;

		case 2: //設定画面
		$buttons = [
			[
				"text" => "閉じる"
			],
		];

		$data = [
			'type' => "form",
			'title' => "§l設定画面",
			'content' => "未実装です",
			'buttons' => $buttons
		];
		$cache = [0];
		break;

		case 3: //ステータス
		$buttons = [
			[
				"text" => "閉じる"
			],
		];
		$kill = AccountProvider::get()->getKill($this->player);
		$death = AccountProvider::get()->getDeath($this->player);
		$killraito = AccountProvider::get()->getKillRatio($this->player);

		$data = [
			'type' => "form",
			'title' => "§lステータス",
			'content' => "未実装です",
			'buttons' => $buttons,
		];
		$cache = [0];
		break;

		case 4:
		$buttons =[
			[
				"text" => "閉じる",
			],
		];
		$cache = [0];

		$data = [
			'type' => "form",
			'title' => "§l検索",
			'content' => "未実装です",
			'buttons' => $buttons
		];

		}

		if($cache !== []){
			$this->lastSendData = $data;
			$this->cache = $cache;
			$this->show($id, $data);
		}
	}
}