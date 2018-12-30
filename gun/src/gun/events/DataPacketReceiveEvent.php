<?php

namespace gun\events;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\entity\Attribute;
use pocketmine\item\Item;

use pocketmine\network\mcpe\protocol\ProtocolInfo as Info;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;

use gun\Callback;
use gun\weapons\beam;
use gun\gameManager;
use gun\form\formManager;

class DataPacketReceiveEvent extends Events {

	public function __construct($api){
		parent::__construct($api);
		$this->beam = new beam($api);
		$this->form = new formManager();
	}
	
	public function call($ev){
		$p = $ev->getPlayer();
		$pk = $ev->getPacket();
		
		switch($pk::NETWORK_ID){
			case Info::INVENTORY_TRANSACTION_PACKET:
				if($pk->transactionType !== 2) return false;
				if($p->ticks['touch'] > ($time = round(microtime(true), 5))){
		        		return false;
		        	}
		        	if($pk->transactionType === InventoryTransactionPacket::TYPE_USE_ITEM_ON_ENTITY){
		        		//if(is_null($pk->trData->entityRuntimeId)) return false;
		        		formManager::touch($pk, $p);
		        		break;
		        	}
		        	if(!isset($p->gun) or !gameManager::getTeam($p->getName())) break;
				$p->ticks['touch'] = $time + round(0.25, 5);
				if($p->isSneaking() and ($gun = $p->gun) !== null and !$p->reloading) {
					$p->reloading = true;
					$p->sendPopup("---reloading---");
					$this->schedule->scheduleDelayedTask(new CallBack([$this, 'reload'], [$p, $gun]), $gun['reload'] * 20);
					break;
				}
				if($p->shot){
					$p->shot = false;
					break;
				}
				if(!$p->reloading and ($gun = $p->gun) !== null and $p->getInventory()->getItemInHand()->getId() !== 261){
					$p->shot = true;
					$this->beam->shot($p, $gun);
					break;
				}
			break;
			case Info::MODAL_FORM_RESPONSE_PACKET:
				formManager::receive($pk, $p);
			break;	
		}
	}
	
	public function reload($p, $gun){
		$p->ammo = (int) $gun['max_ammo'];
		$p->reloading = false;
		$p->sendPopUp("Completed");
	}
}

					
			
		
