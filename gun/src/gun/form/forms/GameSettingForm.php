<?php

namespace gun\form\forms;

use gun\provider\TDMSettingProvider;
use gun\game\games\TeamDeathMatch;

class GameSettingForm extends Form
{

	const MODE_EDIT = 0;
	const MODE_MAKE = 1;
	const MODE_DELETE = 2;
	const MODE_INSPECTION= 3;

	private $mode = "";
	private $gameType = "";
	private $worldName = "";
	private $provider;

	public function send(int $id)
	{
		$cache = [];
		switch($id)
		{
			case 1://メイン画面
				$buttons = [
						[
							"text" => "§lチームデスマッチ -TeamDeathMatch-§r§8\nチームに分かれてキル数を競う"
						]
						];
				$cache = [2];
				$data = [
					'type'    => "form",
					'title'   => "§lゲーム設定画面",
					'content' => "編集したいゲームを設定してください",
					'buttons' => $buttons
				];
				break;

			case 2:
				switch($this->lastData)
				{
					case 0:
						$type = TeamDeathMatch::GAME_ID;
						$this->provider = TDMSettingProvider::get();
						break;
					default:
						$this->close();
						return true;
				}
				$this->gameType = $type;
				$buttons = [
								[
									"text" => "§lステージの追加§r§8\n新たにステージを作成します"
								],
								[
									"text" => "§lステージの編集§r§8\n既存ステージの設定データを編集します"
								],
								[
									"text" => "§lステージの削除§r§8\nステージを削除します"
								],
								[
									"text" => "§lステージの視察§r§8\nステージを見に行きます"
								]
							];
				$data = [
					'type'    => "form",
					'title'   => "§lゲーム設定画面",
					'content' => "",
					'buttons' => $buttons
				];
				$cache = [3, 3, 3, 3];
				break;

			case 3:
				$content = [];

				switch($this->lastData)
				{
					case 0:
						$mode = self::MODE_MAKE;
						$content[] = ["type" => "input", "text" => "追加するステージのワールド名(フォルダ名)を入力してください\n\nワールド名", "placeholder" => "ワールド名を入力(フォルダー名)"];
						$cache = [4];
						break;
					case 1:
						$mode = self::MODE_EDIT;
						$array = [];
						foreach ($this->provider->getAllData() as $key => $value) {
							$array[] = (string) $value["Stage_Name"];
						}
						$content[] = ["type" => "dropdown", "text" => "編集するステージを選択してください\n\nステージ名", "options" => $array];
						$cache = [4];
						break;
					case 2:
						$mode = self::MODE_DELETE;
						$array = [];
						foreach ($this->provider->getAllData() as $key => $value) {
							$array[] = (string) $value["Stage_Name"];
						}
						$content[] = ["type" => "dropdown", "text" => "削除するステージを選択してください\n\nステージ名", "options" => $array];
						$cache = [11];
						break;
					case 3:
						$mode = self::MODE_INSPECTION;
						$array = [];
						foreach ($this->provider->getAllData() as $key => $value) {
							$array[] = (string) $value["Stage_Name"];
						}
						$content[] = ["type" => "dropdown", "text" => "視察するステージを選択してください\n\nステージ名", "options" => $array];
						$cache = [21];
						break;
					default:
						$this->close();
						return true;
				}
				$this->mode = $mode;
				$data = [
					'type'=>'custom_form',
					'title'   => "§lゲーム設定画面",
					'content' => $content
				];
				break;

			case 4:
				switch($this->mode)
				{
					case self::MODE_MAKE:
						if($this->lastData === [])
						{
							$this->sendModal("§lゲーム設定画面", "§cError>>§fワールド名を入力してください", $label1 = "閉じる", $label2 = "再入力", $jump1 = 0, $jump2 = 3);
							return true;				
						}
						if(isset(TDMSettingProvider::get()->getAllData()[$this->lastData[0]]))
						{
							$this->sendModal("§lゲーム設定画面", "§cError>>§f既に存在するステージです", $label1 = "閉じる", $label2 = "再入力", $jump1 = 0, $jump2 = 3);
							return true;
						}
						if(!file_exists($this->plugin->getServer()->getDataPath() . "worlds/" . $this->lastData[0] . "/"))
						{
							$this->sendModal("§lゲーム設定画面", "§cError>>§fワールドデータが存在しません", $label1 = "閉じる", $label2 = "再入力", $jump1 = 0, $jump2 = 3);
							return true;
						}
						$worldName = $this->lastData[0];
						break;
					case self::MODE_EDIT:
						$worldName = array_keys(TDMSettingProvider::get()->getAllData())[$this->lastData[0]];
						break;
				}
				$this->worldName = $worldName;

				$data = TDMSettingProvider::get()->getStageData($this->worldName);
				$content = [];
				switch($this->gameType)
				{
					case TeamDeathMatch::GAME_ID:
						$content[] = ["type" => "input", "text" => "ステージ名(装飾コード使用可)", "default" => $this->mode === self::MODE_EDIT ? (string) $data["Stage_Name"] :(string) ""];
						$content[] = ["type" => "input", "text" => "試合時間(秒単位)", "default" => $this->mode === self::MODE_EDIT ? (string) $data["Game_Time"] :(string) 900];
						$content[] = ["type" => "input", "text" => "待機時間(秒単位)", "default" => $this->mode === self::MODE_EDIT ? (string) $data["Waiting_Time"] :(string) 60];
						$content[] = ["type" => "input", "text" => "キルカウント上限", "default" => $this->mode === self::MODE_EDIT ? (string) $data["Killcount_Max"] :(string) 60];
						$content[] = ["type" => "input", "text" => "プレイヤーの体力", "default" => $this->mode === self::MODE_EDIT ? (string) $data["Player_Health"] :(string) 40];
						$content[] = ["type" => "input", "text" => "チーム名(1)", "default" => $this->mode === self::MODE_EDIT ? (string) $data["Team_Data"][0]["name"] :(string) "Red"];
						$content[] = ["type" => "input", "text" => "チームカラー(1)", "default" => $this->mode === self::MODE_EDIT ? (string) $data["Team_Data"][0]["decoration"] :(string) "§c"];
						$content[] = ["type" => "input", "text" => "スポーンポイントX(1)", "default" => $this->mode === self::MODE_EDIT ? (string) $data["Team_Data"][0]["spawn"]["x"] :(string) 0];
						$content[] = ["type" => "input", "text" => "スポーンポイントY(1)", "default" => $this->mode === self::MODE_EDIT ? (string) $data["Team_Data"][0]["spawn"]["y"] :(string) 5];
						$content[] = ["type" => "input", "text" => "スポーンポイントZ(1)", "default" => $this->mode === self::MODE_EDIT ? (string) $data["Team_Data"][0]["spawn"]["z"] :(string) 0];
						$content[] = ["type" => "slider", "text" => "帽子の色成分§cR§f(1)", "min" => 0, "max" => 255, "default" => $this->mode === self::MODE_EDIT ? $data["Team_Data"][0]["color"]["r"] : 255];
						$content[] = ["type" => "slider", "text" => "帽子の色成分§aG§f(1)", "min" => 0, "max" => 255, "default" => $this->mode === self::MODE_EDIT ? $data["Team_Data"][0]["color"]["g"] : 0];
						$content[] = ["type" => "slider", "text" => "帽子の色成分§bB§f(1)", "min" => 0, "max" => 255, "default" => $this->mode === self::MODE_EDIT ? $data["Team_Data"][0]["color"]["b"] : 0];
						$content[] = ["type" => "input", "text" => "チーム名(2)", "default" => $this->mode === self::MODE_EDIT ? (string) $data["Team_Data"][1]["name"] :(string) "Blue"];
						$content[] = ["type" => "input", "text" => "チームカラー(2)", "default" => $this->mode === self::MODE_EDIT ? (string) $data["Team_Data"][1]["decoration"] :(string) "§b"];
						$content[] = ["type" => "input", "text" => "スポーンポイントX(2)", "default" => $this->mode === self::MODE_EDIT ? (string) $data["Team_Data"][1]["spawn"]["x"] :(string) 0];
						$content[] = ["type" => "input", "text" => "スポーンポイントY(2)", "default" => $this->mode === self::MODE_EDIT ? (string) $data["Team_Data"][1]["spawn"]["y"] :(string) 5];
						$content[] = ["type" => "input", "text" => "スポーンポイントZ(2)", "default" => $this->mode === self::MODE_EDIT ? (string) $data["Team_Data"][1]["spawn"]["z"] :(string) 0];
						$content[] = ["type" => "slider", "text" => "帽子の色成分§cR§f(2)", "min" => 0, "max" => 255, "default" => $this->mode === self::MODE_EDIT ? $data["Team_Data"][1]["color"]["r"] : 0];
						$content[] = ["type" => "slider", "text" => "帽子の色成分§aG§f(2)", "min" => 0, "max" => 255, "default" => $this->mode === self::MODE_EDIT ? $data["Team_Data"][1]["color"]["g"] : 0];
						$content[] = ["type" => "slider", "text" => "帽子の色成分§bB§f(2)", "min" => 0, "max" => 255, "default" => $this->mode === self::MODE_EDIT ? $data["Team_Data"][1]["color"]["b"] : 255];
						break;
				}
				$data = [
					'type'=>'custom_form',
					'title'   => "§lゲーム設定画面",
					'content' => $content
				];

				$cache = [5];
				break;

			case 5:
				$data = [];
				switch($this->gameType)
				{
					case TeamDeathMatch::GAME_ID:
						$data["Stage_Name"] = (string) $this->lastData[0];
						$data["Game_Time"] = (int) $this->lastData[1];
						$data["Waiting_Time"] = (int) $this->lastData[2];
						$data["Killcount_Max"] = (int) $this->lastData[3];
						$data["Player_Health"] = (int) $this->lastData[4];
						$data["Team_Data"][0]["name"] = (string) $this->lastData[5];
						$data["Team_Data"][0]["decoration"] = (string) $this->lastData[6];
						$data["Team_Data"][0]["spawn"]["x"] = (int) $this->lastData[7];
						$data["Team_Data"][0]["spawn"]["y"] = (int) $this->lastData[8];
						$data["Team_Data"][0]["spawn"]["z"] = (int) $this->lastData[9];
						$data["Team_Data"][0]["color"]["r"] = (int) $this->lastData[10];
						$data["Team_Data"][0]["color"]["g"] = (int) $this->lastData[11];
						$data["Team_Data"][0]["color"]["b"] = (int) $this->lastData[12];
						$data["Team_Data"][1]["name"] = (string) $this->lastData[13];
						$data["Team_Data"][1]["decoration"] = (string) $this->lastData[14];
						$data["Team_Data"][1]["spawn"]["x"] = (int) $this->lastData[15];
						$data["Team_Data"][1]["spawn"]["y"] = (int) $this->lastData[16];
						$data["Team_Data"][1]["spawn"]["z"] = (int) $this->lastData[17];
						$data["Team_Data"][1]["color"]["r"] = (int) $this->lastData[18];
						$data["Team_Data"][1]["color"]["g"] = (int) $this->lastData[19];
						$data["Team_Data"][1]["color"]["b"] = (int) $this->lastData[20];
						break;
				}
				$this->provider->setStageData($this->worldName, $data);
				$this->sendModal("§lゲーム設定画面", $this->mode === self::MODE_EDIT ? "ステージの編集が完了しました" : "ステージの追加が完了しました", $label1 = "閉じる", $label2 = "更にステージを追加/編集する", $jump1 = 0, $jump2 = 1);
				return true;

			case 11:
				$this->worldName = array_keys($this->provider->getAllData())[$this->lastData[0]];
				$text = "選択したステージ>> " . $this->provider->getAllData()[$this->worldName]["Stage_Name"] . "\n" . "本当に削除しますか?";
				$this->sendModal("§lゲーム設定画面", $text, $label1 = "§c削除する", $label2 = "戻る", $jump1 = 12, $jump2 = 1);
				break;

			case 12:
				if(count($this->provider->getAllData()) === 1)
				{
					$this->sendModal("§lゲーム設定画面", "これ以上削除できません", $label1 = "戻る", $label2 = "閉じる", $jump1 = 1, $jump2 = 0);
					return true;
				}
				$this->provider->unsetStageData($this->worldName);
				$this->sendModal("§lゲーム設定画面", "削除しました", $label1 = "戻る", $label2 = "閉じる", $jump1 = 1, $jump2 = 0);
				return true;

			case 21:
				$this->worldName = array_keys($this->provider->getAllData())[$this->lastData[0]];
				if(!$this->plugin->getServer()->isLevelLoaded($this->worldName)) $this->plugin->getServer()->loadLevel($this->worldName);
				$this->player->teleport($this->plugin->getServer()->getLevelByName($this->worldName)->getSafeSpawn());
				break;

		}

		if($cache !== []){
			$this->lastSendData = $data;
			$this->cache = $cache;
			$this->show($id, $data);
		}
	}

}