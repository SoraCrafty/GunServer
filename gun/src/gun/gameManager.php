<?php

namespace gun;

use pocketmine\math\Vector3;
use pocketmine\level\Position;

use gun\Callback;
use gun\fireworks\item\Fireworks;

class gameManager 
{
    //ここらへん(定数とか)は後でConfigで設定できるようににする(コードも粗め)
    //'Red' => new Vector3(82,5,65), 'Blue' => new Vector3(-5,4,7), 'spawn' => new Vector3(-2,4,-2)
    const TEAM_NAME = [
                    0 => [
                        "name" => "Red",
                        "decoration" => "§c",
                        "spawn" => [82, 5 , 65]
                        ],
                    1 => [
                        "name" => "Blue",
                        "decoration" => "§b",
                        "spawn" => [-5, 4, 7]
                        ]
                    ];

    const WAITING_TIME = 10;//秒単位

    const GAME_TIME = 0.1 * 60;//秒単位

    const KILLCOUNT_MAX = 5;

    /*Mainクラスのオブジェクト*/
    private $plugin;
    /*ゲームの進行状態*/
    private $TimeTableStatus = -1;//-1が初期値
    /*チームメンバー*/
    private $teamMembers = [
                        0 => [],
                        1 => []
                        ];
    /*キルカウント*/
    private $killCount = [
                        0 => 0,
                        1 => 0
                        ];

    public function __construct($plugin)
    {
        $this->plugin = $plugin;
        $this->TimeTable();
    }

    public function TimeTable()
    {
        $this->TimeTableStatus++;

        switch($this->TimeTableStatus)
        {
            case 0:
                $this->WaitingTask();
                return true;

            case 1:
                $this->setTeamMembers();
                $this->setSpawns();
                $this->gotoStageAll();
                $this->plugin->getServer()->broadcastTitle("§l§cGame Start!!§r", "§f試合開始!!", 5, 20, 10);
                $this->GameTask(self::GAME_TIME);
                return true;

            case 2:
                $this->ResultTask(-1);
                return true;

            case 3:
                $this->setDefaultSpawns();
                $this->gotoLobbyAll();
                $this->resetGameStatus();
                $this->TimeTable();//最初に戻る
                return true;
        }


    }

    /*ゲーム開始可能人数になってからどれだけ経ったか*/
    private $waitingCount = 0;

    public function WaitingTask()
    {
        if(count($this->plugin->getServer()->getOnlinePlayers()) >= 2)
        {
            $this->waitingCount += 1;
            if($this->waitingCount >= self::WAITING_TIME)
            {
                $this->waitingCount = 0;
                $this->TimeTable();
                return true;
            }
            $this->plugin->BossBar->setTitle("§lゲーム開始まであと§c" . (self::WAITING_TIME - $this->waitingCount) . "§f秒");
            $this->plugin->BossBar->setPercentage((self::WAITING_TIME - $this->waitingCount) / self::WAITING_TIME);
        }
        else
        {
            $this->waitingCount = 0;
            $this->plugin->BossBar->setPercentage(1);
            $this->plugin->BossBar->setTitle("§l§a参加者を待っています…");
        }

        $this->plugin->getScheduler()->scheduleDelayedTask(new Callback([$this, 'WaitingTask'], []), 20);
    }

    public function setTeamMembers()
    {
        $players = $this->plugin->getServer()->getOnlinePlayers();
        shuffle($players);
        foreach ($players as $player) {
            $this->lotteryTeam($player);
        }
    }

    public function lotteryTeam($player)
    {
        $team = (count($this->teamMembers[0]) <= count($this->teamMembers[1])) ? 0 : 1;
        $this->teamMembers[$team][] = $player;
        $player->sendMessage("§aGAME>>§fあなたは" . self::TEAM_NAME[$team]["decoration"] . self::TEAM_NAME[$team]["name"] . "§fになりました");
    }

    public function setDefaultSpawns()
    {
        foreach ($this->teamMembers as $team => $members) 
        {
            foreach ($members as $player) 
            {
                $player->setSpawn($this->plugin->getServer()->getDefaultLevel()->getSpawnLocation());
            }    
        }
    }

    public function setSpawns()
    {
        foreach ($this->teamMembers as $team => $members) 
        {
            foreach ($members as $player) 
            {
                $this->setSpawn($player, $team);
            }    
        }
    }

    public function setSpawn($player, $team)
    {
        $player->setSpawn(new Vector3(self::TEAM_NAME[$team]["spawn"][0], self::TEAM_NAME[$team]["spawn"][1], self::TEAM_NAME[$team]["spawn"][2]));
    }

    public function gotoStageAll()
    {
        foreach ($this->teamMembers as $team => $members) 
        {
            foreach ($members as $player) 
            {
                $this->gotoStage($player, $team);
            }    
        }   
    }

    public function gotoStage($player, $team)
    {
        $player->teleport(new Vector3(self::TEAM_NAME[$team]["spawn"][0], self::TEAM_NAME[$team]["spawn"][1], self::TEAM_NAME[$team]["spawn"][2]));
    }

    public function gotoLobbyAll()
    {
        foreach ($this->teamMembers as $team => $members) 
        {
            foreach ($members as $player) 
            {
                $this->gotoLobby($player);
            }    
        }   
    }

    public function gotoLobby($player)
    {
        $player->teleport($this->plugin->getServer()->getDefaultLevel()->getSpawnLocation());
    }

    public function GameTask($time)
    {
        if($this->TimeTableStatus !== 1) return true;

        if($time <= 0)
        {
            $this->TimeTable();
            return true;
        }

        $this->plugin->BossBar->setPercentage($time / self::GAME_TIME);
        $this->plugin->BossBar->setTitle("§a試合時間残り>>§f" . floor($time / 60) . " : " . str_pad(round($time % 60), 2, "0", STR_PAD_LEFT) . 
                                         "  §aキルカウント>>§f" . self::TEAM_NAME[0]["decoration"] . self::TEAM_NAME[0]["name"] . "§f:" . $this->killCount[0] . "/" . self::KILLCOUNT_MAX . " vs " . 
                                                             self::TEAM_NAME[1]["decoration"] . self::TEAM_NAME[1]["name"] . "§f:" . $this->killCount[1] . "/" . self::KILLCOUNT_MAX);
        $time--;
        $this->plugin->getScheduler()->scheduleDelayedTask(new Callback([$this, 'GameTask'], [$time]), 20);
    }

    public function ResultTask($phase)
    {
        $phase++;

        switch($phase)
        {

            case 0:
                $this->plugin->getServer()->broadcastTitle("§l§cGame Set!!§r", "§f試合終了!!", 5, 20, 10);
                $this->plugin->BossBar->setPercentage(0);
                $this->plugin->BossBar->setTitle("§8<<試合終了>>§f" . 
                                                 "  §aキルカウント>>§f" . self::TEAM_NAME[0]["decoration"] . self::TEAM_NAME[0]["name"] . "§f:" . $this->killCount[0] . "/" . self::KILLCOUNT_MAX . " vs " . 
                                                                      self::TEAM_NAME[1]["decoration"] . self::TEAM_NAME[1]["name"] . "§f:" . $this->killCount[1] . "/" . self::KILLCOUNT_MAX);
                $this->plugin->getScheduler()->scheduleDelayedTask(new Callback([$this, 'ResultTask'], [$phase]), 40);
                return true;

            case 1:
                $winteam = $this->killCount[0] > $this->killCount[1] ? 0 : 1;
                $this->plugin->getServer()->broadcastMessage("§aGAME>>§f" . self::TEAM_NAME[1]["decoration"] . self::TEAM_NAME[1]["name"] . "§fチームの勝利!!");
                $this->plugin->getScheduler()->scheduleDelayedTask(new Callback([$this, 'ResultTask'], [$phase]), 10);
                return true;

            default://ここも改善したい
                $types = [
                            Fireworks::TYPE_SMALL_SPHERE,
                            Fireworks::TYPE_HUGE_SPHERE,
                            Fireworks::TYPE_STAR,
                            Fireworks::TYPE_CREEPER_HEAD,
                            Fireworks::TYPE_BURST
                        ];
                $colors = [
                            Fireworks::COLOR_RED,
                            Fireworks::COLOR_BLUE,
                            Fireworks::COLOR_PINK,
                            Fireworks::COLOR_GREEN,
                            Fireworks::COLOR_YELLOW,
                            Fireworks::COLOR_LIGHT_AQUA,
                            Fireworks::COLOR_GOLD,
                            Fireworks::COLOR_WHITE
                        ];
                foreach ($this->teamMembers as $team => $members) 
                {
                    foreach ($members as $player) 
                    {
                        if($player->isOnline())
                        {
                            shuffle($types);
                            shuffle($colors);
                            $this->plugin->Fireworks->spawn(
                                                            Position::fromObject($player->getPosition()->add(mt_rand(-6, 6), mt_rand(5 ,15) * 0.1, mt_rand(-6, 6)), $player->getLevel()), 
                                                            1,
                                                            $types[0],
                                                            $colors[0]
                                                            );
                        }
                    }    
                }
                $this->plugin->BossBar->setPercentage(($phase - 2) / 8);//2~10
                $this->plugin->getScheduler()->scheduleDelayedTask(new Callback([$this, 'ResultTask'], [$phase]), 20);
                return true;

            case 11:
                $this->TimeTable();
                return true;
        }
    }

    public function resetGameStatus()
    {
        $this->TimeTableStatus = -1;
        $this->teamMembers = [
                        0 => [],
                        1 => []
                            ];
        $this->killCount = [
                        0 => 0,
                        1 => 0
                        ];
    }

    public function getTeam($player) {
        foreach ($this->teamMembers as $team => $members) {
            if (array_search($player, $members) !== false) {
                return $team;
            }
        }
        return false;
    }

    public function addKillCount($team)
    {
        $this->killCount[$team]++;

        if($this->killCount[$team] >= self::KILLCOUNT_MAX && $this->TimeTableStatus === 1)
        {
            $this->TimeTable();
        }
    }

    public function isGaming()
    {
        return $this->TimeTableStatus === 1;  
    }

	/*private static $instance;

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
    	}*/
    	
}
		
