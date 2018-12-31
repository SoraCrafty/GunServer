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
		$this->game = false;
		$this->server->broadcastMessage('§aGAME>>§f1分半後にゲームが開始されます');
		$this->spawn = ['Red' => new Vector3(82,5,65), 'Blue' => new Vector3(-5,4,7), 'spawn' => new Vector3(-2,4,-2)];
	}

	public function gamestart(){
		$this->killcount = [ 'Red' => 0, 'Blue' => 0];
		$this->team = ['Red' => [], 'Blue' => []];
		$this->game = true;
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
		$this->game = false;
		foreach($players as $player){
			$this->setName($player);
			$player->teleport($this->spawn['spawn']);
			$player->setSpawn($this->spawn['spawn']);
		}
		$this->schedule->scheduleDelayedTask(new CallBack([$this, 'gamestart'], []), 90 * 20);
		$this->server->broadcastMessage('§aGAME>>§f1分半後にゲームが開始されます');
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
   	 
   	 public static function addMember($player) {
   	 	if(!self::$instance->game) return false;
	    		$user = $player->getName();
            		foreach (self::$instance->team as $min_team => $members) {
                		if (!isset($amount) or $amount > count($members)) {
                	   		$amount = count($members);
                	    		$team = $min_team;
                		}
            		}
            		self::$instance->team[$team][] = $user;
            		self::$instance->setName($player);
           		$player->sendMessage('あなたは'.$team.'チームです');
            		$player->teleport(self::$instance->spawn[$team]);
            		$player->setSpawn(self::$instance->spawn[$team]);
	}
	
	public static function removeMember($user) {
        	$team = self::$instance->getTeam($user);
        	if ($team != false) {
            		$team_index = array_search($user, self::$instance->team[$team]);
            		unset(self::$instance->team[$team][$team_index]);
            		$num = 0;
            		foreach (self::$instance->team as $te => $members) {
                		if (count($members) === 0) {
                    			++$num;
                		}
            		}
            		if ($num === 1) {
                		self::$instance->gameend();
                		foreach (self::$instance->server->getOnlinePlayers() as $player) {
                    			$player->teleport(self::$instance->spawn);
                		}
            		}
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
		
