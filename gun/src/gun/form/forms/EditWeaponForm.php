<?php

namespace gun\form\forms;

use pocketmine\item\Item;

use gun\form\FormManager;

use gun\weapons\WeaponManager;
use gun\weapons\AssaultRifle;
use gun\weapons\SniperRifle;
use gun\weapons\HandGun;

class EditWeaponForm extends Form
{
/*雑*/
	const MODE_EDIT = 0;
	const MODE_MAKE = 1;

	private $mode = "";
	private $weaponType = "";

	public function send(int $id)
	{
		$cache = [];
		switch($id)
		{
			case 1://メイン画面
				$buttons = [
							[
								"text" => "§lアサルトライフル -AssaultRifle-§r§8\n全自動射撃能力を持つ自動小銃"
							],
							[
								"text" => "§lスナイパーライフル -SniperRifle-§r§8\n狙撃用に特化した小銃"
							],
							[
								"text" => "§lハンドガン -HandGun-§r§8\n片手で射撃するためにデザインされた銃"
							]
						];
				$data = [
					'type'    => "form",
					'title'   => "§l武器編集/追加画面",
					'content' => "追加/編集したい武器種を選択してください",
					'buttons' => $buttons
				];
				$cache = [2, 2, 2];
				break;

			case 2:
				switch($this->lastData)
				{
					case 0:
						$type = AssaultRifle::WEAPON_ID;
						break;
					case 1:
						$type = SniperRifle::WEAPON_ID;
						break;
					case 2:
						$type = HandGun::WEAPON_ID;
						break;
					default:
						$this->close();
						return true;
				}
				$this->weaponType = $type;
				$buttons = [
								[
									"text" => "§l武器の追加§r§8\n新たに武器を作成します"
								],
								[
									"text" => "§l武器の編集§r§8\n既存武器の設定データを編集します"
								]
							];
				$data = [
					'type'    => "form",
					'title'   => "§l武器編集/追加画面",
					'content' => "選択した武器種>>" . WeaponManager::getName($this->weaponType),
					'buttons' => $buttons
				];
				$cache = [3, 3, 3];
				break;

			case 3:
				$content = [];

				switch($this->lastData)
				{
					case 0:
						$mode = self::MODE_MAKE;
						$content[] = ["type" => "input", "text" => "追加する武器の武器IDを入力してください\n\n武器ID", "placeholder" => "武器IDを入力(他とかぶらないものにしてください)"];
						break;
					case 1:
						$mode = self::MODE_EDIT;
						$content[] = ["type" => "dropdown", "text" => "編集する武器の武器IDを入力してください\n\n武器ID", "options" => array_keys(WeaponManager::getAllData($this->weaponType))];
						break;
					default:
						$this->close();
						return true;
				}
				$this->mode = $mode;
				$data = [
					'type'=>'custom_form',
					'title'   => "§l武器編集/追加画面",
					'content' => $content
				];
				$cache = [4];
				break;

			case 4:
				switch($this->mode)
				{
					case self::MODE_MAKE:
						if($this->lastData === [])
						{
							$this->sendModal("§l武器編集/追加画面", "§cError>>§f武器IDを入力してください", $label1 = "閉じる", $label2 = "再入力", $jump1 = 0, $jump2 = 3);
							return true;				
						}
						if(!is_null(WeaponManager::getData($this->weaponType, $this->lastData[0])))
						{
							$this->sendModal("§l武器編集/追加画面", "§cError>>§f既に存在する武器IDです", $label1 = "閉じる", $label2 = "再入力", $jump1 = 0, $jump2 = 3);
							return true;
						}
						$weaponId = $this->lastData[0];
						break;
					case self::MODE_EDIT:
						$weaponId = array_keys(WeaponManager::getAllData($this->weaponType))[$this->lastData[0]];
						break;
				}
				$this->weaponId = $weaponId;

				$data = WeaponManager::getData($this->weaponType, $this->weaponId);
				$content = [];
				$content[] = ["type" => "label", "text" => "武器ID >> " . $this->weaponId];
				$content[] = ["type" => "input", "text" => "武器名(装飾コード使用可)", "default" => $this->mode === self::MODE_EDIT ? $data["Item_Information"]["Item_Name"]:""];
				$content[] = ["type" => "input", "text" => "アイテムID", "default" => $this->mode === self::MODE_EDIT ? (string) $data["Item_Information"]["Item_Id"]:""];
				$content[] = ["type" => "input", "text" => "アイテムのダメージ値", "default" => $this->mode === self::MODE_EDIT ? (string) $data["Item_Information"]["Item_Damage"]:""];
				$content[] = ["type" => "input", "text" => "武器の説明文", "default" => $this->mode === self::MODE_EDIT ? $data["Item_Information"]["Item_Lore"]:""];
				switch($this->weaponType)
				{
					case AssaultRifle::WEAPON_ID:
						$content[] = ["type" => "slider", "text" => "発射レート", "min" => 1, "max" => 20, "default" => $this->mode === self::MODE_EDIT ? $data["Shooting"]["Delay_Between_Shots"] : 1];
						$content[] = ["type" => "slider", "text" => "ダメージ", "min" => 1, "max" => 20, "default" => $this->mode === self::MODE_EDIT ? $data["Shooting"]["Shooting_Damage"] : 1];
						$content[] = ["type" => "slider", "text" => "射程", "min" => 1, "max" => 100, "default" => $this->mode === self::MODE_EDIT ? $data["Shooting"]["Shooting_Range"] : 1];
						$content[] = ["type" => "slider", "text" => "反動(入力値の1/10倍されます)", "min" => 0, "max" => 50, "default" => $this->mode === self::MODE_EDIT ? $data["Shooting"]["Recoil_Amount"] * 10 : 10];
						$content[] = ["type" => "slider", "text" => "弾ブレ(入力値の1/10倍されます)", "min" => 0, "max" => 30, "default" => $this->mode === self::MODE_EDIT ? $data["Shooting"]["Bullet_Spread"] * 10 : 10];
						$content[] = ["type" => "toggle", "text" => "スニーク時の機能のon/off", "default" => $this->mode === self::MODE_EDIT ? $data["Sneak"]["Enable"] : true];
						$content[] = ["type" => "toggle", "text" => "スニーク時に反動を消す", "default" => $this->mode === self::MODE_EDIT ? $data["Sneak"]["No_Recoil"] : true];
						$content[] = ["type" => "slider", "text" => "スニーク時の弾ブレ(入力値の1/10倍されます)", "min" => 0, "max" => 30, "default" => $this->mode === self::MODE_EDIT ? $data["Sneak"]["Bullet_Spread"] * 10 : 0];
						$content[] = ["type" => "toggle", "text" => "リロード機能のon/off(offにすると弾数が無限になります)", "default" => $this->mode === self::MODE_EDIT ? $data["Reload"]["Enable"] : true];
						$content[] = ["type" => "slider", "text" => "弾数", "min" => 1, "max" => 100, "default" => $this->mode === self::MODE_EDIT ? $data["Reload"]["Reload_Amount"] : 30];
						$content[] = ["type" => "slider", "text" => "リロードにかかる時間", "min" => 1, "max" => 100, "default" => $this->mode === self::MODE_EDIT ? $data["Reload"]["Reload_Duration"] : 20];
						$content[] = ["type" => "slider", "text" => "移動速度(入力値の1/10倍されます)", "min" => 0, "max" => 20, "default" => $this->mode === self::MODE_EDIT ? $data["Move"]["Move_Speed"] * 10 : 10];
						break;
					case SniperRifle::WEAPON_ID:
						$content[] = ["type" => "slider", "text" => "発射後のクールタイム", "min" => 0, "max" => 100, "default" => $this->mode === self::MODE_EDIT ? $data["Shooting"]["Cooltime_Between_Shots"] : 20];
						$content[] = ["type" => "slider", "text" => "ダメージ", "min" => 1, "max" => 20, "default" => $this->mode === self::MODE_EDIT ? $data["Shooting"]["Shooting_Damage"] : 1];
						$content[] = ["type" => "slider", "text" => "射程", "min" => 1, "max" => 100, "default" => $this->mode === self::MODE_EDIT ? $data["Shooting"]["Shooting_Range"] : 1];
						$content[] = ["type" => "slider", "text" => "反動(入力値の1/10倍されます)", "min" => 0, "max" => 50, "default" => $this->mode === self::MODE_EDIT ? $data["Shooting"]["Recoil_Amount"] * 10 : 10];
						$content[] = ["type" => "slider", "text" => "弾ブレ(入力値の1/10倍されます)", "min" => 0, "max" => 30, "default" => $this->mode === self::MODE_EDIT ? $data["Shooting"]["Bullet_Spread"] * 10 : 10];
						$content[] = ["type" => "toggle", "text" => "スニーク時の機能のon/off", "default" => $this->mode === self::MODE_EDIT ? $data["Sneak"]["Enable"] : true];
						$content[] = ["type" => "toggle", "text" => "スニーク時に反動を消す", "default" => $this->mode === self::MODE_EDIT ? $data["Sneak"]["No_Recoil"] : true];
						$content[] = ["type" => "slider", "text" => "スニーク時の弾ブレ(入力値の1/10倍されます)", "min" => 0, "max" => 30, "default" => $this->mode === self::MODE_EDIT ? $data["Sneak"]["Bullet_Spread"] * 10 : 0];
						$content[] = ["type" => "toggle", "text" => "リロード機能のon/off(offにすると弾数が無限になります)", "default" => $this->mode === self::MODE_EDIT ? $data["Reload"]["Enable"] : true];
						$content[] = ["type" => "slider", "text" => "弾数", "min" => 1, "max" => 100, "default" => $this->mode === self::MODE_EDIT ? $data["Reload"]["Reload_Amount"] : 30];
						$content[] = ["type" => "slider", "text" => "リロードにかかる時間", "min" => 1, "max" => 100, "default" => $this->mode === self::MODE_EDIT ? $data["Reload"]["Reload_Duration"] : 20];
						$content[] = ["type" => "slider", "text" => "移動速度(入力値の1/10倍されます)", "min" => 0, "max" => 20, "default" => $this->mode === self::MODE_EDIT ? $data["Move"]["Move_Speed"] * 10 : 10];
						break;
					case HandGun::WEAPON_ID:
						$content[] = ["type" => "slider", "text" => "ダメージ", "min" => 1, "max" => 20, "default" => $this->mode === self::MODE_EDIT ? $data["Shooting"]["Shooting_Damage"] : 1];
						$content[] = ["type" => "slider", "text" => "射程", "min" => 1, "max" => 100, "default" => $this->mode === self::MODE_EDIT ? $data["Shooting"]["Shooting_Range"] : 1];
						$content[] = ["type" => "slider", "text" => "弾ブレ(入力値の1/10倍されます)", "min" => 0, "max" => 30, "default" => $this->mode === self::MODE_EDIT ? $data["Shooting"]["Bullet_Spread"] * 10 : 10];
						$content[] = ["type" => "toggle", "text" => "スニーク時の機能のon/off", "default" => $this->mode === self::MODE_EDIT ? $data["Sneak"]["Enable"] : true];
						$content[] = ["type" => "slider", "text" => "スニーク時の弾ブレ(入力値の1/10倍されます)", "min" => 0, "max" => 30, "default" => $this->mode === self::MODE_EDIT ? $data["Sneak"]["Bullet_Spread"] * 10 : 0];
						$content[] = ["type" => "toggle", "text" => "リロード機能のon/off(offにすると弾数が無限になります)", "default" => $this->mode === self::MODE_EDIT ? $data["Reload"]["Enable"] : true];
						$content[] = ["type" => "slider", "text" => "弾数", "min" => 1, "max" => 100, "default" => $this->mode === self::MODE_EDIT ? $data["Reload"]["Reload_Amount"] : 30];
						$content[] = ["type" => "slider", "text" => "リロードにかかる時間", "min" => 1, "max" => 100, "default" => $this->mode === self::MODE_EDIT ? $data["Reload"]["Reload_Duration"] : 20];
						$content[] = ["type" => "slider", "text" => "移動速度(入力値の1/10倍されます)", "min" => 0, "max" => 20, "default" => $this->mode === self::MODE_EDIT ? $data["Move"]["Move_Speed"] * 10 : 10];
						break;
				}
				$data = [
					'type'=>'custom_form',
					'title'   => "§l武器編集/追加画面",
					'content' => $content
				];

				$cache = [5];
				break;

			case 5:
				$data = [];
				$data["Item_Information"] = [
												"Item_Name" => $this->lastData[1],
												"Item_Id" => (int) $this->lastData[2],
												"Item_Damage" => (int) $this->lastData[3],
												"Item_Lore" => $this->lastData[4]
											];
				switch($this->weaponType)
				{
					case AssaultRifle::WEAPON_ID:
						$data["Shooting"] = [
												"Delay_Between_Shots" => $this->lastData[5],
												"Shooting_Damage" => $this->lastData[6],
												"Shooting_Range" => $this->lastData[7],
												"Recoil_Amount" => $this->lastData[8] / 10,
												"Bullet_Spread" => $this->lastData[9] / 10
											];
						$data["Sneak"] = [
												"Enable" => $this->lastData[10],
												"No_Recoil" => $this->lastData[11],
												"Bullet_Spread" => $this->lastData[12] / 10
											];
						$data["Reload"] = [
												"Enable" => $this->lastData[13],
												"Reload_Amount" => $this->lastData[14],
												"Reload_Duration" => $this->lastData[15]
											];
						$data["Move"] = [
												"Move_Speed" => $this->lastData[16] / 10
											];
						break;
					case SniperRifle::WEAPON_ID:
						$data["Shooting"] = [
												"Cooltime_Between_Shots" => $this->lastData[5],
												"Shooting_Damage" => $this->lastData[6],
												"Shooting_Range" => $this->lastData[7],
												"Recoil_Amount" => $this->lastData[8] / 10,
												"Bullet_Spread" => $this->lastData[9] / 10
											];
						$data["Sneak"] = [
												"Enable" => $this->lastData[10],
												"No_Recoil" => $this->lastData[11],
												"Bullet_Spread" => $this->lastData[12] / 10
											];
						$data["Reload"] = [
												"Enable" => $this->lastData[13],
												"Reload_Amount" => $this->lastData[14],
												"Reload_Duration" => $this->lastData[15]
											];
						$data["Move"] = [
												"Move_Speed" => $this->lastData[16] / 10
											];
						break;
					case HandGun::WEAPON_ID:
						$data["Shooting"] = [
												"Shooting_Damage" => $this->lastData[5],
												"Shooting_Range" => $this->lastData[6],
												"Bullet_Spread" => $this->lastData[7] / 10
											];
						$data["Sneak"] = [
												"Enable" => $this->lastData[8],
												"Bullet_Spread" => $this->lastData[9] / 10
											];
						$data["Reload"] = [
												"Enable" => $this->lastData[10],
												"Reload_Amount" => $this->lastData[11],
												"Reload_Duration" => $this->lastData[12]
											];
						$data["Move"] = [
												"Move_Speed" => $this->lastData[13] / 10
											];
						break;
				}
				WeaponManager::setData($this->weaponType, $this->weaponId, $data);
				$this->sendModal("§l武器編集/追加画面", $this->mode === self::MODE_EDIT ? "武器の編集が完了しました" : "武器の追加が完了しました", $label1 = "閉じる", $label2 = "更に武器を追加/編集する", $jump1 = 0, $jump2 = 1);
				break;

			default:
				$this->close();
				return true;
		}

		if($cache !== []){
			$this->lastSendData = $data;
			$this->cache = $cache;
			$this->show($id, $data);
		}
	}

}
		
