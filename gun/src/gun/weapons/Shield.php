<?php

namespace gun\weapons;

class Shield extends UniqueWeapon
{
	/*シールドの武器カテゴリ*/
	const CATEGORY = self::CATEGORY_SUB;
	/*シールドのID*/
	const WEAPON_ID = "shield";
	/*武器種の名称*/
	const WEAPON_NAME = "Shield";
	/*Loreに書く数値*/
	const ITEM_LORE = [
					"Move" =>[
								"Move_Speed" => "移動速度"
							]
					];
	/*デフォルト武器のデータ*/
	const DEFAULT_DATA = [
							"Item_Information" => [
										"Item_Name" => "シールド",
										"Item_Id" => 351,
										"Item_Damage" => 16,
										"Item_Lore" => "地面に設置し、敵の攻撃を防ぐことができる"
										],
							"Item_Use" => [
										"Cool_Time" => 0
									],
							"Shield" => [
									],
							"Move" =>[
										"Move_Speed" => 0.8
									]
						];
}