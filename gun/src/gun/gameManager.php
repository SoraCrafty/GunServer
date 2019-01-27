<?php

namespace gun;

use pocketmine\math\Vector3;
use pocketmine\level\Position;
use pocketmine\network\mcpe\protocol\LevelEventPacket;

use gun\Callback;
use gun\data\playerData;
use gun\fireworks\item\Fireworks;

use gun\provider\ProviderManager;
use gun\provider\GameSettingProvider;
class gameManager 
{
    /*Mainクラスのオブジェクト*/
    private $plugin;
    /*GameSettingProvider*/
    private $provider;
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
    /*キルストリーク*/
    private $killstreak = [];

    public function __construct($plugin)
    {
        $this->plugin = $plugin;
        $this->provider = ProviderManager::get(GameSettingProvider::PROVIDER_ID);
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
                $this->setNameTagsAll();
                $this->plugin->getServer()->broadcastTitle("§l§cGame Start!!§r", "§f試合開始!!", 5, 20, 10);
                $this->playSoundIndivudually(LevelEventPacket::EVENT_SOUND_TOTEM, 0);
                $this->GameTask($this->provider->getGameTime());
                return true;

            case 2:
                $this->ResultTask(-1);
                return true;

            case 3:
                $this->setDefaultSpawns();
                $this->givePrizeAll();//賞金を渡したかったので書き加えました
                $this->gotoLobbyAll();
                $this->setDefaultNameTagsAll();
                $this->resetGameStatus();
                $this->TimeTable();//最初に戻る
                return true;
        }


    }

    /*ゲーム開始まであと何秒か*/
    private $waitingCount;

    public function WaitingTask()
    {
        if(count($this->plugin->getServer()->getOnlinePlayers()) >= 2)
        {
            $this->waitingCount--;
            if($this->waitingCount === 0)
            {
                $this->waitingCount = $this->provider->getWaitingTime();
                $this->TimeTable();
                return true;
            }
            if($this->waitingCount <= 5)
            {
                $this->playSoundIndivudually(LevelEventPacket::EVENT_SOUND_ANVIL_FALL, 0);
            }
            $this->plugin->BossBar->setTitle("§lゲーム開始まであと§a" . ($this->waitingCount) . "§f秒");
            $this->plugin->BossBar->setPercentage($this->waitingCount / $this->provider->getWaitingTime());
        }
        else
        {
            $this->waitingCount = $this->provider->getWaitingTime();
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
        $player->sendMessage("§aGAME>>§fあなたは" . $this->provider->getTeamNameDecoration($team) . $this->provider->getTeamName($team) . "§fになりました");
    }

    public function setDefaultSpawns()
    {
        foreach ($this->teamMembers as $team => $members) 
        {
            foreach ($members as $player) 
            {
                if($player->isOnline()) $player->setSpawn($this->plugin->getServer()->getDefaultLevel()->getSpawnLocation());
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
        if($player->isOnline())
        {
            $vectorArray = $this->provider->getTeamSpawn($team);
            $player->setSpawn(new Vector3($vectorArray["x"], $vectorArray["y"], $vectorArray["z"]));
        }
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
        if($player->isOnline())
        {
            $vectorArray = $this->provider->getTeamSpawn($team);
            $player->teleport(new Vector3($vectorArray["x"], $vectorArray["y"], $vectorArray["z"]));
        }
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
        if($player->isOnline()) $player->teleport($this->plugin->getServer()->getDefaultLevel()->getSpawnLocation());
    }

    public function GameTask($time)
    {
        if($this->TimeTableStatus !== 1) return true;

        if($time <= 0)
        {
            $this->TimeTable();
            return true;
        }

        $this->plugin->BossBar->setPercentage($time / $this->provider->getGameTime());
        $this->plugin->BossBar->setTitle("§a試合時間残り>>§f" . str_pad(floor($time / 60), 2, "0", STR_PAD_LEFT) . " : " . str_pad(round($time % 60), 2, "0", STR_PAD_LEFT) . 
                                         "  §aキルカウント>>§f" . $this->provider->getTeamNameDecoration(0) . $this->provider->getTeamName(0) . "§f:" . $this->killCount[0] . "/" . $this->provider->getMaxKillCount() . " vs " . 
                                                              $this->provider->getTeamNameDecoration(1) . $this->provider->getTeamName(1) . "§f:" . $this->killCount[1] . "/" . $this->provider->getMaxKillCount());
        $time--;
        $this->plugin->getScheduler()->scheduleDelayedTask(new Callback([$this, 'GameTask'], [$time]), 20);
    }

    public function ResultTask($phase)
    {
        $phase++;

        switch($phase)
        {

            case 0:
            	$this->playSoundIndivudually(LevelEventPacket::EVENT_SOUND_TOTEM, 0);
                $this->plugin->getServer()->broadcastTitle("§l§cGame Set!!§r", "§f試合終了!!", 5, 20, 10);
                $this->plugin->BossBar->setPercentage(0);
                $this->plugin->BossBar->setTitle("§8<<試合終了>>§f" . 
                                         "  §aキルカウント>>§f" . $this->provider->getTeamNameDecoration(0) . $this->provider->getTeamName(0) . "§f:" . $this->killCount[0] . "/" . $this->provider->getMaxKillCount() . " vs " . 
                                                              $this->provider->getTeamNameDecoration(1) . $this->provider->getTeamName(1) . "§f:" . $this->killCount[1] . "/" . $this->provider->getMaxKillCount());
                $this->plugin->getScheduler()->scheduleDelayedTask(new Callback([$this, 'ResultTask'], [$phase]), 40);
                return true;

            case 1:
                $winteam = $this->killCount[0] > $this->killCount[1] ? 0 : 1;
                $this->plugin->getServer()->broadcastMessage("§aGAME>>§f" . $this->provider->getTeamNameDecoration($winteam) . $this->provider->getTeamName($winteam) . "§fチームの勝利!!");
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
        $this->killstreak = [];
    }

    public function setTeam($player, $team)
    {
        foreach ($this->teamMembers[$team] as $key => $member) {
            //if($player == $member) return true; エラー吐く
            if($player->getName() === $member->getName())
            {
                $this->teamMembers[$team][$key] = $player;
                return true;
            }
        }

        $this->teamMembers[$team][] = $player;
    }

    public function getTeam($player) {//要改善
        foreach ($this->teamMembers as $team => $members) 
        {
            foreach ($members as $member) {
                //if($player == $member) return true; エラー吐く
                if($player->getName() === $member->getName())
                {
                    return $team;
                }
            }
        }
        return false;
    }

    public function addKillCount($team)
    {
        $this->killCount[$team]++;

        if($this->killCount[$team] >= $this->provider->getMaxKillCount() && $this->TimeTableStatus === 1)
        {
            $this->TimeTable();
        }
    }

    /*キルストリークらへん雑いんで時間ある時改善*/

    public function addKillStreak($player)
    {
        $name = $player->getName();

        if(!isset($this->killstreak[$name])) $this->killstreak[$name] = 0;
        $this->killstreak[$name]++;

        if($this->killstreak[$name] >= 2)$player->sendTip("\n\n§a" . $this->killstreak[$name] . "§fキルストリークを達成しました");

        if($this->killstreak[$name] >= 5)
        {
            $this->plugin->getServer()->broadcastMessage("§aGAME>>§f" . $player->getNameTag() . "§fが" . $this->killstreak[$name] . "キルストリークを達成しました");
        }
    }

    public function resetKillStreak($player)
    {
        unset($this->killstreak[$player->getName()]);
    }

    public function isGaming()
    {
        return $this->TimeTableStatus === 1;  
    }

    /*PMMPのアプデきたら処理変える*/
    public function playSoundIndivudually($id, $pitch){
        foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
            $pk = new LevelEventPacket();
            $pk->evid = $id;
            $pk->position = $player->getPosition();
            $pk->data = $pitch;
            $player->dataPacket($pk);
        }
    }

    /*別クラスに移動したほうがいいかも*/

    public function setNameTagsAll()
    {
        foreach ($this->teamMembers as $team => $members) 
        {
            foreach ($members as $player) 
            {
                $this->setNameTags($player, $team);
            }    
        }   
    }

    public function setNameTags($player, $team)
    {
    	if($player->isOnline())
    	{
	    	$tag = $this->provider->getTeamNameDecoration($team) . $player->getName() . "§f";
	    	$player->setNameTag($tag);
	    	$player->setDisplayName($tag);
    	}
    }

    public function setDefaultNameTagsAll()
    {
        foreach ($this->teamMembers as $team => $members) 
        {
            foreach ($members as $player) 
            {
                $this->setDefaultNameTags($player);
            }    
        }   
    }

    public function setDefaultNameTags($player)
    {
    	if($player->isOnline())
    	{
	    	$tag = $player->getName();
	    	$player->setNameTag($tag);
	    	$player->setDisplayName($tag);
    	}
    }
    
    /*賞金*/
    public function givePrizeAll()
    {
    	/*$playerdata = playerData::getPlayerData();
    	$winteam = $this->killCount[0] > $this->killCount[1] ? 0 : 1;
    	foreach ($this->teamMembers[$winteam] as $player) 
        {
        	$playerdata->setAccount($player->getName(), 'money', $playerdata->getAccount($player->getName())['money'] + 2000);
            $player->sendMessage('§aGAME>>§f>>賞金を贈与しました');
        }*/   
    }
    
    
}
		
