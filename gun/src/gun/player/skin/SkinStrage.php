<?php

namespace gun\player\skin;

class SkinStrage
{

	const CAPE_LIST = [
						"basic_red" => "basicRed",
						"babayuta_cup" => "babayutaCup",
						"ghost" => "ghostCape"
					];

	private $plugin;
	private $capes = [];

	public function __construct($plugin)
	{
		$this->plugin = $plugin;
		$this->loadCapes();
	}

	/*Cape*/

	private function loadCapes()
	{
		foreach (self::CAPE_LIST as $key => $value) {
			$path = __DIR__ . "/cape/{$value}.png";
			$img = @imagecreatefrompng($path);
			$this->capes[$key] = '';
			$lx = (int) @getimagesize($path)[0];
			$ly = (int) @getimagesize($path)[1];
			for ($y = 0; $y < $ly; $y++) {
			    for ($x = 0; $x < $lx; $x++) {
			        $rgba = @imagecolorat($img, $x, $y);
			        $a = ((~((int)($rgba >> 24))) << 1) & 0xff;
			        $r = ($rgba >> 16) & 0xff;
			        $g = ($rgba >> 8) & 0xff;
			        $b = $rgba & 0xff;
			        $this->capes[$key] .= chr($r) . chr($g) . chr($b) . chr($a);
			    }
			}
			@imagedestroy($img);
		}
	}

	public function getCapeData($key)
	{
		$data = '';
		if(isset($this->capes[$key])) $data = $this->capes[$key];
		
		return $data;
	}

}