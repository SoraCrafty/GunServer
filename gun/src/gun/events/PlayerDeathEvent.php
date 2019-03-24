<?php
namespace gun\events;

use pocketmine\Player;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\item\Item;

use gun\gameManager;
use gun\weapons\Weapon;
use gun\weapons\WeaponManager;
use gun\provider\AccountProvider;

class PlayerDeathEvent extends Events {/*要改善*/

	public function __construct($plugin){
		parent::__construct($plugin);
	}

	public function call($event){
		$event->setKeepInventory(true);
		$player = $event->getPlayer();
		if($player->getSpawn()->getLevel()!=$player->getPosition()->getLevel()) $this->plugin->getServer()->getPluginManager()->callEvent(new EntityTeleportEvent($player, $player->getPosition(), $player->getSpawn()->getLevel()->getSafeSpawn()));
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
				$message = '§c§l⚔§r§7[§f' . $killer->getDisplayName() . '§r§7]§f---> §7[§f' . $weaponname . '§r§7]§f--->§7 [§r§f' . $player->getDisplayName() . '§r§7]§r';
				$event->setDeathMessage($message);
				$this->plugin->discordManager->sendConvertedMessage('§c§l⚔§r§7[§f' . $killer->getName() . '§r§7]§f---> §7[§f' . $weaponname . '§7]§f--->§7 [§r§f' . $player->getName() . '§r§7]§r', "game");
				AccountProvider::get()->addDeath($player, 1);
				AccountProvider::get()->addKill($killer, 1);
			}
		}
	}
}
