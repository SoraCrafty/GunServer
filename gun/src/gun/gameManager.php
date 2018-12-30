<?php

namespace gun;

use pocketmine\math\Vector3;
use gun\Callback;

class gameManager {

	private static $instance;

	public function __construct($plugin){
		self::$instance = $this;
		$this->plugin = $plugin;
		$this->server = $plugin->getServer();
		$this->schedule = $plugin->getScheduler();
		$this->schedule->scheduleDelayedTask(new CallBack([$this, 'gamestart'], []), 120 * 20);
		$this->killcount = [ 'Red' => 0, 'Blue' => 0];
		$this->team = ['Red' => [], 'Blue' => []];
		$this->server->broadcastMessage('§aGAME>>§f1分半後にゲームが開始されます');
		$this->spawn = ['Red' => new Vector3(82,5,65), 'Blue' => new Vector3(-5,4,7), 'spawn' => new Vector3(35,6,165)];
	}

	public function gamestart(){
		$this->killcount = [ 'Red' => 0, 'Blue' => 0];
		$this->team = ['Red' => [], 'Blue' => []];
		$players = $this->server->getOnlinePlayers();
		shuffle($players);
		foreach($players as $player){
			$this->addMember($player);
		}
		$this->server->broadcastMessage('§aGAME>>§fゲームスタート!');
		//てレポートする処理
	}
	
	public function gameend($win){
		$this->server->broadcastMessage('§aGAME>>§f勝ったのは'.$win.'チームです');
		$players = $this->server->getOnlinePlayers();
		$this->killcount = [ 'Red' => 0, 'Blue' => 0];
		$this->team = ['Red' => [], 'Blue' => []];
		foreach($players as $player){
			$this->setName($player);
			$player->teleport($this->spawn['spawn']);
			$player->setSpawn($this->spawn['spawn']);
		}
		$this->schedule->scheduleDelayedTask(new CallBack([$this, 'gamestart'], []), 90 * 20);
		$this->server->broadcastMessage('§aGAME>>§f1分半後にゲームが開始されます');
	}
	
	public function addMember($player) {
	    	$user = $player->getName();
            	foreach ($this->team as $min_team => $members) {
                	if (!isset($amount) or $amount > count($members)) {
                   		$amount = count($members);
                    		$team = $min_team;
                	}
            	}
            	$this->team[$team][] = $user;
            	$this->setName($player);
           	$player->sendMessage('あなたは'.$team.'チームです');
            	$player->teleport($this->spawn[$team]);
            	$player->setSpawn($this->spawn[$team]);
    	}
    	
    	public function setName($player){ 
    		$name = $player->getName();
    		$team = $this->getTeam($name);
    	       	$color = $this->getColor($team);
        	$player->setNameTag($color . $name.'§f');
        	$player->setDisplayName($color . $name.'§f');
    	}
    	
    	public function getColor($team) {
        	switch ($team) {
            	case 1:
            	case "Red":
                	return "§c";
            		break;
            	case 2:
            	case "Blue":
                	return "§9";
            		break;
		case false:
            		return "§f";
            		break;

        	}
   	 }
    	
    	public static function getTeam($username) {
        	foreach (self::$instance->team as $team => $members) {
            		if (array_search($username, $members) !== false) {
                		return $team;
            		}
        	}
        	return false;
    	}
    	
    	public static function addKillCount($team){
    		self::$instance->killcount[$team]++;
    		if(self::$instance->killcount[$team] >=50){
    			self::$instance->gameend($team);
    		}
    	}
    	
	public static function getKillCount($team){
    		return self::$instance->killcount[$team];
    	}
    	
    	public static function toSpawn($player){
    		$team = self::$instance::getTeam($player->getName());
    		if($team)
    		$player->teleport(self::$instance->spawn[$team]);
    	}
    	
}
		
