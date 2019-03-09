<?php

namespace gun\form\forms;

use pocketmine\item\Item;

use gun\form\FormManager;

use gun\provider\ProviderManager;
use gun\provider\MainWeaponShop;
use gun\provider\SubWeaponShop;
use gun\provider\AccountProvider;

use gun\weapons\WeaponManager;
use gun\weapons\Weapon;
use gun\weapons\AssaultRifle;
use gun\weapons\SniperRifle;
use gun\weapons\ShotGun;
use gun\weapons\HandGun;

class TestFireForm extends Form
{

	private $weaponCategory;
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
					'title'   => "§l射撃場",
					'content' => "試し打ちしたい武器種を選択してください",
					'buttons' => $buttons
				];
				$cache = [2, 2, 2, 2];
				break;

			case 2://購入武器選択画面
				$buttons = [];
				switch($this->lastData)
				{
					case 0:
						$type = AssaultRifle::WEAPON_ID;
						$provider = ProviderManager::get(MainWeaponShop::PROVIDER_ID);
						$this->weaponCategory = Weapon::CATEGORY_MAIN;
						break;
					case 1:
						$type = SniperRifle::WEAPON_ID;
						$provider = ProviderManager::get(MainWeaponShop::PROVIDER_ID);
						$this->weaponCategory = Weapon::CATEGORY_MAIN;
						break;
					case 2:
						$type = ShotGun::WEAPON_ID;
						$provider = ProviderManager::get(MainWeaponShop::PROVIDER_ID);
						$this->weaponCategory = Weapon::CATEGORY_MAIN;
						break;
					case 3:
						$type = HandGun::WEAPON_ID;
						$provider = ProviderManager::get(SubWeaponShop::PROVIDER_ID);
						$this->weaponCategory = Weapon::CATEGORY_SUB;
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
					'title'   => "§l射撃場",
					'content' => "試し打ちしたい武器を選択してください",
					'buttons' => $buttons
				];
				break;

			case 3:
				switch ($this->weaponCategory) {
					case Weapon::CATEGORY_MAIN:
						$provider = MainWeaponShop::get();
						break;
					
					case Weapon::CATEGORY_SUB:
						$provider = SubWeaponShop::get();
						break;

					default:
						$this->close();
						break;
				}
				$weaponId = array_keys($provider->getItems($this->weaponType))[$this->lastData];
				$item = WeaponManager::get($this->weaponType, $weaponId);
				$this->player->getInventory()->addItem($item);
				if($this->weaponType === SniperRifle::WEAPON_ID && !$this->player->getInventory()->contains(Item::get(262, 0, 1))) $this->player->getInventory()->addItem(Item::get(262, 0, 1));
				$this->close();
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
