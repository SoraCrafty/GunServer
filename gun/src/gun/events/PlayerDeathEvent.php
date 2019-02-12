<?php
namespace gun\events;

use pocketmine\Player;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\Item;

use gun\gameManager;
use gun\weapons\Weapon;
use gun\weapons\WeaponManager;

class PlayerDeathEvent extends Events {/*要改善*/

	public function __construct($plugin){
		parent::__construct($plugin);
	}

	public function call($event){
		$event->setKeepInventory(true);
		$player = $event->getPlayer();

		if($player->getLastDamageCause() instanceof EntityDamageByEntityEvent){
			$killer = $player->getLastDamageCause()->getDamager();
			if($killer instanceof Player)
			{
				$weapon = $killer->getInventory()->getItemInHand();
				$tag = $weapon->getNamedTagEntry(Weapon::TAG_WEAPON);
				if(!is_null($tag))
				{
					$object = WeaponManager::getObject($tag->getTag(Weapon::TAG_WEAPON_ID)->getValue());
					$data = $object->getData($tag->getTag(Weapon::TAG_TYPE)->getValue());
					if(!is_null($data)) $weaponname = $data["Item_Information"]["Item_Name"];
					else $weaponname = "KILL";
				}
				else
				{
					$weaponname = "KILL";
				}
				$event->setDeathMessage('§c§l⚔§r§7[§f' . $killer->getDisplayName() . '§r§7]§f ---> §7[§f' . $weaponname . '§7] §f--->§7 [§r§f' . $player->getDisplayName() . '§r§7]§r');
				$killerteam = $this->plugin->gameManager->getTeam($killer);
				$playerteam = $this->plugin->gameManager->getTeam($player);
				if($killerteam !== false && $playerteam !== false && $this->plugin->gameManager->isGaming()){
					$item = Item::get(322, 0, 1);
			        $killer->getInventory()->addItem($item);
					$this->plugin->gameManager->addKillCount($killerteam);
					$this->plugin->gameManager->addKillStreak($killer);
					$this->plugin->gameManager->resetKillStreak($player);
					/*$this->playerdata->setAccount($killer->getName(), 'kill', $this->playerdata->getAccount($killer->getName())['kill'] + 1);
					$this->playerdata->setAccount($killer->getName(), 'money', $this->playerdata->getAccount($killer->getName())['money'] + 100);
					$this->playerdata->setAccount($player->getName(), 'death', $this->playerdata->getAccount($player->getName())['death'] + 1);*/
				}
			}
		}

	}
}
