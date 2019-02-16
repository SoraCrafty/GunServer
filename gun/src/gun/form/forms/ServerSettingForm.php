<?php

namespace gun\form\forms;

use pocketmine\network\mcpe\protocol\ServerSettingsResponsePacket;

use gun\provider\AccountProvider;

class ServerSettingForm extends Form
{

	public function send(int $id)
	{
		$content = [];
		$content[] = [
			'type' => "step_slider",
			'text' => "連射タイプの武器の操作感度(タップ操作時のみ効果あり)",
			'steps' => [/*"Low", */"Normal", "High"],
			'default' => AccountProvider::get()->getSetting($this->player, "sensitivity"),
		];
		$data = [
			'type'    => 'custom_form',
			'title'   => 'BattleFront2',
			'icon'    => [
				'type' => 'path',
				'data' => "textures/server/servericon"
			],
			'content' => $content
		];
		$pk = new ServerSettingsResponsePacket();
		$pk->formId = $id;
		$pk->formData = json_encode(
			$data,
			JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING | JSON_UNESCAPED_UNICODE
		);
		$this->player->dataPacket($pk);
	}

	public function response(int $id, $data)
	{
		AccountProvider::get()->setSetting($this->player, "sensitivity", $data[0]);
		return true;
	}

}
		
