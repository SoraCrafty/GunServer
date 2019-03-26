<?php
namespace gun\skills\ult;

class Ult extends Skills{

	public function get($type){
		if(!isset($this->skills[$type])) return null;

		$item = parent::get($type);

		$nbt = $item->getNamedTagEntry(Skills::TAG_SKILL);
		$nbt->setInt(Weapon::TAG_CT, $this->skills[$type]::CT);	
		$item->setNamedTagEntry($nbt);
		return $item;
	}
}
