<?php

namespace gun\form\forms;

use gun\form\FormManager;

use gun\provider\ProviderManager;
use gun\provider\MainWeaponShop;

use gun\weapons\WeaponManager;
use gun\weapons\AssaultRifle;
use gun\weapons\SniperRifle;

class MainShopForm extends Form
{

	private $weaponType = "";
	private $weaponId = "";

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
						]
						];
				$data = [
					'type'    => "form",
					'title'   => "§lMainWeaponShop(メイン武器屋)",
					'content' => "購入したい武器種を選択してください",
					'buttons' => $buttons
				];
				$cache = [2, 2];
				break;

			case 2://購入武器選択画面
				$buttons = [];
				$provider = ProviderManager::get(MainWeaponShop::PROVIDER_ID);
				switch($this->lastData)
				{
					case 0:
						$type = AssaultRifle::WEAPON_ID;
						break;
					case 1:
						$type = SniperRifle::WEAPON_ID;
						break;
					default:
						$this->close();
						return true;
				}
				$this->weaponType = $type;
				$weaponData = WeaponManager::getAllData($type);
				foreach ($provider->getItems($type) as $key => $value) {
					$buttons[] = [
									"text" => "§l" . $weaponData[$key]["Item_Information"]["Item_Name"] . " §7｜§e " . $value . "P§r§8\n" . $weaponData[$key]["Item_Information"]["Item_Lore"]
								];
					$cache[] = 3;
				}
				$data = [
					'type'    => "form",
					'title'   => "§lMainWeaponShop(メイン武器屋)",
					'content' => "購入したい武器を選択してください",
					'buttons' => $buttons
				];
				break;

			case 3://武器購入確認画面(雑いので改善したい)
				$weaponId = array_keys(WeaponManager::getAllData($this->weaponType))[$this->lastData];
				$this->weaponId = $weaponId;
				$provider = ProviderManager::get(MainWeaponShop::PROVIDER_ID);
				$price = $provider->getPrice($this->weaponType, $weaponId);
				$content = "この武器を§e". $price ."P§fで購入しますか?\n▼詳細";
				$content .= "\n§a武器名 : " . WeaponManager::getData($this->weaponType, $weaponId)["Item_Information"]["Item_Name"];
				foreach (WeaponManager::getObject($this->weaponType)::ITEM_LORE as $datakey => $data) {
					foreach ($data as $key => $value) {
						$content .= "\n§a{$value} : §f" . WeaponManager::getData($this->weaponType, $weaponId)[$datakey][$key];
					}
				}
				$this->sendModal("§lMainWeaponShop(メイン武器屋)", $content, "購入", "戻る", 4, 1);
				break;

			case 4:
				
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
		
