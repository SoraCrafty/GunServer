<?php

namespace gun\form\forms;

use pocketmine\item\Item;

use gun\form\FormManager;

use gun\weapons\WeaponManager;
use gun\weapons\AssaultRifle;
use gun\weapons\SniperRifle;
use gun\weapons\ShotGun;
use gun\weapons\HandGun;

use gun\provider\MainWeaponShop;

class EditWeaponForm extends Form
{
/*雑*/
	const MODE_EDIT = 0;
	const MODE_MAKE = 1;
	const MODE_DELETE = 2;

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
								"text" => "§lショットガン -ShotGun-§r§8\n多数の小さい弾丸を発射する大型銃"
							],
							[
								"text" => "§lハンドガン -HandGun-§r§8\n片手で射撃するためにデザインされた銃"
							]
						];
				$data = [
					'type'    => "form",
					'title'   => "§l武器編集/追加/削除画面",
					'content' => "追加/編集/削除したい武器種を選択してください",
					'buttons' => $buttons
				];
				$cache = [2, 2, 2, 2];
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
						$type = ShotGun::WEAPON_ID;
						break;
					case 3:
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
								],
								[
									"text" => "§l武器の削除§r§8\n武器を削除します"
								]
							];
				$data = [
					'type'    => "form",
					'title'   => "§l武器編集/追加/削除画面",
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
						$cache = [4];
						break;
					case 1:
						$mode = self::MODE_EDIT;
						$array = [];
						foreach (array_keys(WeaponManager::getAllData($this->weaponType)) as $key => $value) {
							$array[] = (string) $value;
						}
						$content[] = ["type" => "dropdown", "text" => "編集する武器の武器IDを選択してください\n\n武器ID", "options" => $array];
						$cache = [4];
						break;
					case 2:
						$mode = self::MODE_DELETE;
						$array = [];
						foreach (array_keys(WeaponManager::getAllData($this->weaponType)) as $key => $value) {
							$array[] = (string) $value;
						}
						$content[] = ["type" => "dropdown", "text" => "削除する武器の武器IDを選択してください\n\n武器ID", "options" => $array];
						$cache = [11];
						break;
					default:
						$this->close();
						return true;
				}
				$this->mode = $mode;
				$data = [
					'type'=>'custom_form',
					'title'   => "§l武器編集/追加/削除画面",
					'content' => $content
				];
				break;

			case 4:
				switch($this->mode)
				{
					case self::MODE_MAKE:
						if($this->lastData === [])
						{
							$this->sendModal("§l武器編集/追加/削除画面", "§cError>>§f武器IDを入力してください", $label1 = "閉じる", $label2 = "再入力", $jump1 = 0, $jump2 = 3);
							return true;				
						}
						if(!is_null(WeaponManager::getData($this->weaponType, $this->lastData[0])))
						{
							$this->sendModal("§l武器編集/追加/削除画面", "§cError>>§f既に存在する武器IDです", $label1 = "閉じる", $label2 = "再入力", $jump1 = 0, $jump2 = 3);
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
				$content[] = ["type" => "label", "text" => "武器ID >> " . (string) $this->weaponId];
				$content[] = ["type" => "input", "text" => "武器名(装飾コード使用可)", "default" => $this->mode === self::MODE_EDIT ? (string) $data["Item_Information"]["Item_Name"]:""];
				$content[] = ["type" => "input", "text" => "アイテムID", "default" => $this->mode === self::MODE_EDIT ? (string) $data["Item_Information"]["Item_Id"]:""];
				$content[] = ["type" => "input", "text" => "アイテムのダメージ値", "default" => $this->mode === self::MODE_EDIT ? (string) $data["Item_Information"]["Item_Damage"]:""];
				$content[] = ["type" => "input", "text" => "武器の説明文", "default" => $this->mode === self::MODE_EDIT ? (string) $data["Item_Information"]["Item_Lore"]:""];
				switch($this->weaponType)
				{
					case AssaultRifle::WEAPON_ID:
						$content[] = ["type" => "slider", "text" => "発射レート", "min" => 1, "max" => 20, "default" => $this->mode === self::MODE_EDIT ? $data["Shooting"]["Delay_Between_Shots"] : 1];
						$content[] = ["type" => "slider", "text" => "ダメージ", "min" => 1, "max" => 40, "default" => $this->mode === self::MODE_EDIT ? $data["Shooting"]["Shooting_Damage"] : 1];
						$content[] = ["type" => "slider", "text" => "弾速", "min" => 1, "max" => 50, "default" => $this->mode === self::MODE_EDIT ? $data["Shooting"]["Bullet_Speed"] : 5];
						$content[] = ["type" => "slider", "text" => "反動(入力値の1/10倍されます)", "min" => 0, "max" => 50, "default" => $this->mode === self::MODE_EDIT ? $data["Shooting"]["Recoil_Amount"] * 10 : 0];
						$content[] = ["type" => "slider", "text" => "弾ブレ(入力値の1/10倍されます)", "min" => 0, "max" => 100, "default" => $this->mode === self::MODE_EDIT ? $data["Shooting"]["Bullet_Spread"] * 10 : 10];
						$content[] = ["type" => "toggle", "text" => "スニーク時の機能のon/off", "default" => $this->mode === self::MODE_EDIT ? $data["Sneak"]["Enable"] : true];
						$content[] = ["type" => "toggle", "text" => "スニーク時に反動を消す", "default" => $this->mode === self::MODE_EDIT ? $data["Sneak"]["No_Recoil"] : true];
						$content[] = ["type" => "slider", "text" => "スニーク時の弾ブレ(入力値の1/10倍されます)", "min" => 0, "max" => 30, "default" => $this->mode === self::MODE_EDIT ? $data["Sneak"]["Bullet_Spread"] * 10 : 0];
						$content[] = ["type" => "toggle", "text" => "リロード機能のon/off(offにすると弾数が無限になります)", "default" => $this->mode === self::MODE_EDIT ? $data["Reload"]["Enable"] : true];
						$content[] = ["type" => "slider", "text" => "弾数", "min" => 1, "max" => 100, "default" => $this->mode === self::MODE_EDIT ? $data["Reload"]["Reload_Amount"] : 30];
						$content[] = ["type" => "slider", "text" => "リロードにかかる時間", "min" => 1, "max" => 100, "default" => $this->mode === self::MODE_EDIT ? $data["Reload"]["Reload_Duration"] : 20];
						$content[] = ["type" => "slider", "text" => "移動速度(入力値の1/10倍されます)", "min" => 0, "max" => 20, "default" => $this->mode === self::MODE_EDIT ? $data["Move"]["Move_Speed"] * 10 : 10];
						break;
					case SniperRifle::WEAPON_ID:
					case HandGun::WEAPON_ID:
						$content[] = ["type" => "slider", "text" => "発射後のクールタイム", "min" => 1, "max" => 100, "default" => $this->mode === self::MODE_EDIT ? $data["Shooting"]["Cooltime_Between_Shots"] : 20];
						$content[] = ["type" => "slider", "text" => "ダメージ", "min" => 1, "max" => 40, "default" => $this->mode === self::MODE_EDIT ? $data["Shooting"]["Shooting_Damage"] : 1];
						$content[] = ["type" => "slider", "text" => "弾速", "min" => 1, "max" => 50, "default" => $this->mode === self::MODE_EDIT ? $data["Shooting"]["Bullet_Speed"] : 5];
						$content[] = ["type" => "slider", "text" => "反動(入力値の1/10倍されます)", "min" => 0, "max" => 50, "default" => $this->mode === self::MODE_EDIT ? $data["Shooting"]["Recoil_Amount"] * 10 : 0];
						$content[] = ["type" => "slider", "text" => "弾ブレ(入力値の1/10倍されます)", "min" => 0, "max" => 100, "default" => $this->mode === self::MODE_EDIT ? $data["Shooting"]["Bullet_Spread"] * 10 : 10];
						$content[] = ["type" => "toggle", "text" => "スニーク時の機能のon/off", "default" => $this->mode === self::MODE_EDIT ? $data["Sneak"]["Enable"] : true];
						$content[] = ["type" => "toggle", "text" => "スニーク時に反動を消す", "default" => $this->mode === self::MODE_EDIT ? $data["Sneak"]["No_Recoil"] : true];
						$content[] = ["type" => "slider", "text" => "スニーク時の弾ブレ(入力値の1/10倍されます)", "min" => 0, "max" => 30, "default" => $this->mode === self::MODE_EDIT ? $data["Sneak"]["Bullet_Spread"] * 10 : 0];
						$content[] = ["type" => "toggle", "text" => "リロード機能のon/off(offにすると弾数が無限になります)", "default" => $this->mode === self::MODE_EDIT ? $data["Reload"]["Enable"] : true];
						$content[] = ["type" => "slider", "text" => "弾数", "min" => 1, "max" => 100, "default" => $this->mode === self::MODE_EDIT ? $data["Reload"]["Reload_Amount"] : 30];
						$content[] = ["type" => "slider", "text" => "リロードにかかる時間", "min" => 1, "max" => 100, "default" => $this->mode === self::MODE_EDIT ? $data["Reload"]["Reload_Duration"] : 20];
						$content[] = ["type" => "slider", "text" => "移動速度(入力値の1/10倍されます)", "min" => 0, "max" => 20, "default" => $this->mode === self::MODE_EDIT ? $data["Move"]["Move_Speed"] * 10 : 10];
						break;
					case ShotGun::WEAPON_ID:
						$content[] = ["type" => "slider", "text" => "発射後のクールタイム", "min" => 1, "max" => 100, "default" => $this->mode === self::MODE_EDIT ? $data["Shooting"]["Cooltime_Between_Shots"] : 20];
						$content[] = ["type" => "slider", "text" => "ダメージ", "min" => 1, "max" => 40, "default" => $this->mode === self::MODE_EDIT ? $data["Shooting"]["Shooting_Damage"] : 1];
						$content[] = ["type" => "slider", "text" => "ダメージ減衰レベル(入力値の1/10倍されます)", "min" => 0, "max" => 80, "default" => $this->mode === self::MODE_EDIT ? $data["Shooting"]["Shooting_Damage_Decay"] : 0];
						$content[] = ["type" => "slider", "text" => "弾速", "min" => 1, "max" => 50, "default" => $this->mode === self::MODE_EDIT ? $data["Shooting"]["Bullet_Speed"] : 5];
						$content[] = ["type" => "slider", "text" => "反動(入力値の1/10倍されます)", "min" => 0, "max" => 50, "default" => $this->mode === self::MODE_EDIT ? $data["Shooting"]["Recoil_Amount"] * 10 : 0];
						$content[] = ["type" => "slider", "text" => "弾ブレ(入力値の1/10倍されます)", "min" => 0, "max" => 100, "default" => $this->mode === self::MODE_EDIT ? $data["Shooting"]["Bullet_Spread"] * 10 : 10];
						$content[] = ["type" => "slider", "text" => "射出量(一度に出る弾の数)", "min" => 1, "max" => 10, "default" => $this->mode === self::MODE_EDIT ? $data["Shooting"]["Bullet_Amount"] : 1];
						$content[] = ["type" => "toggle", "text" => "スニーク時の機能のon/off", "default" => $this->mode === self::MODE_EDIT ? $data["Sneak"]["Enable"] : true];
						$content[] = ["type" => "toggle", "text" => "スニーク時に反動を消す", "default" => $this->mode === self::MODE_EDIT ? $data["Sneak"]["No_Recoil"] : true];
						$content[] = ["type" => "slider", "text" => "スニーク時の弾ブレ(入力値の1/10倍されます)", "min" => 0, "max" => 30, "default" => $this->mode === self::MODE_EDIT ? $data["Sneak"]["Bullet_Spread"] * 10 : 0];
						$content[] = ["type" => "toggle", "text" => "リロード機能のon/off(offにすると弾数が無限になります)", "default" => $this->mode === self::MODE_EDIT ? $data["Reload"]["Enable"] : true];
						$content[] = ["type" => "slider", "text" => "弾数", "min" => 1, "max" => 100, "default" => $this->mode === self::MODE_EDIT ? $data["Reload"]["Reload_Amount"] : 30];
						$content[] = ["type" => "slider", "text" => "リロードにかかる時間", "min" => 1, "max" => 100, "default" => $this->mode === self::MODE_EDIT ? $data["Reload"]["Reload_Duration"] : 20];
						$content[] = ["type" => "slider", "text" => "移動速度(入力値の1/10倍されます)", "min" => 0, "max" => 20, "default" => $this->mode === self::MODE_EDIT ? $data["Move"]["Move_Speed"] * 10 : 10];
						break;
				}
				$content[] = ["type" => "toggle", "text" => "送信後この武器をインベントリへ追加", "default" => false];
				$data = [
					'type'=>'custom_form',
					'title'   => "§l武器編集/追加/削除画面",
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
												"Bullet_Speed" => $this->lastData[7],
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
					case SniperRifle::WEAPON_ID:
						$data["Shooting"] = [
												"Cooltime_Between_Shots" => $this->lastData[5],
												"Shooting_Damage" => $this->lastData[6],
												"Bullet_Speed" => $this->lastData[7],
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
					case ShotGun::WEAPON_ID:
						$data["Shooting"] = [
												"Cooltime_Between_Shots" => $this->lastData[5],
												"Shooting_Damage" => $this->lastData[6],
												"Shooting_Damage_Decay" => $this->lastData[7],
												"Bullet_Speed" => $this->lastData[8],
												"Recoil_Amount" => $this->lastData[9] / 10,
												"Bullet_Spread" => $this->lastData[10] / 10,
												"Bullet_Amount" => $this->lastData[11]
											];
						$data["Sneak"] = [
												"Enable" => $this->lastData[12],
												"No_Recoil" => $this->lastData[13],
												"Bullet_Spread" => $this->lastData[14] / 10
											];
						$data["Reload"] = [
												"Enable" => $this->lastData[15],
												"Reload_Amount" => $this->lastData[16],
												"Reload_Duration" => $this->lastData[17]
											];
						$data["Move"] = [
												"Move_Speed" => $this->lastData[18] / 10
											];
						break;
				}
				WeaponManager::setData($this->weaponType, $this->weaponId, $data);
				if(end($this->lastData))
				{
					$this->player->getInventory()->addItem(WeaponManager::get($this->weaponType, $this->weaponId));
					if($this->weaponType === SniperRifle::WEAPON_ID && !$this->player->getInventory()->contains(Item::get(262))) $this->player->getInventory()->addItem(Item::get(262, 0, 1));
				}
				$this->sendModal("§l武器編集/追加/削除画面", $this->mode === self::MODE_EDIT ? "武器の編集が完了しました" : "武器の追加が完了しました", $label1 = "閉じる", $label2 = "更に武器を追加/編集する", $jump1 = 0, $jump2 = 1);
				return true;

			case 11:
				$this->weaponId = array_keys(WeaponManager::getAllData($this->weaponType))[$this->lastData[0]];
				$text = "選択した武器種>> " . WeaponManager::getName($this->weaponType) . "\n" .
						"選択した武器ID>> " . $this->weaponId . "\n\n" .
						"本当に削除しますか?";
				$this->sendModal("§l武器編集/追加/削除画面", $text, $label1 = "§c削除する", $label2 = "戻る", $jump1 = 12, $jump2 = 1);
				break;

			case 12:
				$this->sendModal("§l武器編集/追加/削除画面", "削除しました", $label1 = "戻る", $label2 = "閉じる", $jump1 = 1, $jump2 = 0);
				WeaponManager::unset($this->weaponType, $this->weaponId);
				MainWeaponShop::get()->deleteItem($this->weaponType, $this->weaponId);
				return true;

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
		
