<?php

namespace gun\game\games;

use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\math\Vector3;
use pocketmine\level\Position;
use pocketmine\nbt\NBT;
use pocketmine\entity\Attribute;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\Effect;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\inventory\ArmorInventory;

use pocketmine\event\entity\EntityDamageByEntityEvent;

use gun\Callback;
use gun\fireworks\item\Fireworks;

use gun\ranking\Ranking;

use gun\provider\ProviderManager;
use gun\provider\TDMSettingProvider;
use gun\provider\AccountProvider;
use gun\provider\MainSettingProvider;
use gun\provider\TestFiringFieldProvider;

use gun\bossbar\BossBar;

use gun\weapons\WeaponManager;

use gun\scoreboard\ScoreboardManager;

class TeamDeathMatch extends Game
{
    const GAME_ID = "tdm";
    const GAME_NAME = "TeamDeathMatch";

    /*Mainクラスのオブジェクト*/
    private $plugin;
    /*TDMSettingProvider*/
    private $provider;
    /*BossBar*/
    private $bossbar;
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
    /*ワールド名*/
    private $levelName = "";

    public function __construct($plugin)
    {
        $this->plugin = $plugin;
        $this->provider = TDMSettingProvider::get();
        $this->bossbar = new BossBar();
        $this->TimeTable();
    }

    public function TimeTable()
    {
        $this->TimeTableStatus++;

        switch($this->TimeTableStatus)
        {
            case 0:
                $this->loadLevel();
                $this->WaitingTask();
                return true;

            case 1:
                $this->joinAll();
                $this->sendTitle("§l§cGame Start!!§r", $this->provider->getStageName($this->levelName), 5, 20, 20);
                $this->plugin->discordManager->sendConvertedMessage('**❗`' . self::getName() . '`が開始されました ステージ：`' . $this->provider->getStageName($this->levelName) . '` **(' . date("m/d H:i") . ')', "game");
                $this->playSoundIndivudually(LevelEventPacket::EVENT_SOUND_TOTEM, 0);
                $this->GameTask($this->provider->getGameTime($this->levelName));
                return true;

            case 2:
                $this->ResultTask(-1);
                return true;

            case 3:
                $this->givePrizeAll();//賞金を渡したかったので書き加えました
                $this->updateRanking();//ランキングアップデート用
                $this->leaveAll();
                $this->resetGameStatus();
                $this->plugin->rebootManager->advanceRebootCount();
                $this->TimeTable();//最初に戻る
                return true;
        }


    }

    /*参加申請者*/
    private $applicants = [];

    public function apply($player)
    {
        $this->applicants[$player->getName()] = $player;
        $this->bossbar->register($player);
        $player->sendMessage('§aGame>>§f参加申請をしました');
    }

    public function unapply($player)
    {
        unset($this->applicants[$player->getName()]);
        $this->bossbar->unregister($player);
        $player->sendMessage('§aGame>>§f参加申請を解除しました');
    }

    public function isApplied($player)
    {
        return isset($this->applicants[$player->getName()]);
    }

    public function joinAll()
    {
        foreach ($this->applicants as $player) {
            $this->join($player);
        }      
    }

    public function join($player)
    {
        $team = $this->getTeam($player);
        if($team === false)
        {
            $this->lotteryTeam($player);
            $team = $this->getTeam($player);
        }
        else
        {
            $this->setTeam($player, $team);
        }
        $this->setSpawn($player, $team);
        $this->gotoStage($player, $team);
        $this->setNameTags($player, $team);
        $this->setInventory($player);
        $this->setHealth($player);
        $this->bossbar->register($player);
        ScoreboardManager::updateLine($player, ScoreboardManager::LINE_TEAM, '§bTeam§f : ' . $this->provider->getTeamNameDecoration($this->levelName, $team) . $this->provider->getTeamName($this->levelName, $team));
        WeaponManager::setPermission($this->plugin, $player, true);
    }

    public function leaveAll()
    {
        foreach ($this->teamMembers as $team => $members) 
        {
            foreach ($members as $player) 
            {
                if($player->isOnline()) $this->leave($player);
            }    
        }
    }

    public function leave_temporary($player)
    {
        $this->plugin->playerManager->setDefaultHealth($player);
        $this->setDefaultSpawn($player);
        $this->gotoLobby($player);
        $this->plugin->playerManager->setLobbyInventory($player);
        $this->setDefaultNameTags($player);
        $this->bossbar->unregister($player);
        $attribute = $player->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED);
        $attribute->setValue($player->isSprinting() ? 1.3 * $attribute->getDefaultValue() : $attribute->getDefaultValue(), false, true);
        WeaponManager::setPermission($this->plugin, $player, false);
    }

    public function leave($player)
    {
        $this->unsetTeam($player);
        $this->leave_temporary($player);
        ScoreboardManager::removeLine($player, ScoreboardManager::LINE_TEAM);
    }

    /*ゲーム開始まであと何秒か*/
    private $waitingCount;

    public function WaitingTask()
    {
        if(count($this->applicants) >= 1)
        {
            $this->waitingCount--;
            if($this->waitingCount === 0)
            {
                $this->waitingCount = $this->provider->getWaitingTime($this->levelName);
                $this->TimeTable();
                return true;
            }
            if($this->waitingCount <= 5)
            {
                $this->playSoundIndivudually_2(LevelSoundEventPacket::SOUND_NOTE);
            }
            $this->bossbar->setTitle("§lゲーム開始まであと§a" . ($this->waitingCount) . "§f秒 §f ステージ>>§a" . $this->provider->getStageName($this->levelName));
            $this->bossbar->setPercentage($this->waitingCount / $this->provider->getWaitingTime($this->levelName));
        }
        else
        {
            $this->waitingCount = $this->provider->getWaitingTime($this->levelName);
            $this->bossbar->setPercentage(1);
            $this->bossbar->setTitle("§l§a参加者を待っています… §f ステージ>>§a" . $this->provider->getStageName($this->levelName));
        }

        $this->plugin->getScheduler()->scheduleDelayedTask(new Callback([$this, 'WaitingTask'], []), 20);
    }

    public function setTeamMembers()
    {
        $players = $this->applicants;
        $this->applicants = [];
        shuffle($players);
        foreach ($players as $player) {
            $this->lotteryTeam($player);
        }
    }

    public function lotteryTeam($player)
    {
        $team = (count($this->teamMembers[0]) <= count($this->teamMembers[1])) ? 0 : 1;
        $this->teamMembers[$team][] = $player;
        $player->sendMessage("§aGAME>>§fあなたは" . $this->provider->getTeamNameDecoration($this->levelName, $team) . $this->provider->getTeamName($this->levelName, $team) . "§fになりました");
    }

    public function setDefaultSpawn($player)
    {
        if($player->isOnline()) $this->plugin->playerManager->setDefaultSpawn($player);
    }

    public function setDefaultSpawns()
    {
        foreach ($this->teamMembers as $team => $members) 
        {
            foreach ($members as $player) 
            {
                $this->setDefaultSpawn($player);
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
            $player->setSpawn($this->provider->getTeamSpawn($this->levelName, $team));
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
            $player->teleport($this->provider->getTeamSpawn($this->levelName, $team));
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
        if($player->isOnline()) $this->plugin->playerManager->gotoLobby($player);
    }

    public function setLobbyInventoryAll()
    {
        foreach ($this->teamMembers as $team => $members) 
        {
            foreach ($members as $player) 
            {
                if($player->isOnline()) $this->plugin->playerManager->setLobbyInventory($player);
            }    
        }      
    }

    public function GameTask($time)
    {
        if($this->TimeTableStatus !== 1) return true;

        if($time <= 0)
        {
            $this->TimeTable();
            return true;
        }

        $this->bossbar->setPercentage($time / $this->provider->getGameTime($this->levelName));
        $this->bossbar->setTitle('§aステージ>>§f' . $this->provider->getStageName($this->levelName) . " §a試合時間残り>>§f" . str_pad(floor($time / 60), 2, "0", STR_PAD_LEFT) . " : " . str_pad(round($time % 60), 2, "0", STR_PAD_LEFT) . 
                                         "  §aキルカウント>>§f" . $this->provider->getTeamNameDecoration($this->levelName, 0) . $this->provider->getTeamName($this->levelName, 0) . "§f:" . $this->killCount[0] . "/" . $this->provider->getMaxKillCount($this->levelName) . " vs " . 
                                                              $this->provider->getTeamNameDecoration($this->levelName, 1) . $this->provider->getTeamName($this->levelName, 1) . "§f:" . $this->killCount[1] . "/" . $this->provider->getMaxKillCount($this->levelName));
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
                $this->sendTitle("§l§cGame Set!!§r", "§f試合終了!!", 5, 20, 10);
                $this->bossbar->setPercentage(0);
                $this->bossbar->setTitle('§aステージ>>§f' . $this->provider->getStageName($this->levelName) . " §8<<試合終了>>§f" . 
                                         "  §aキルカウント>>§f" . $this->provider->getTeamNameDecoration($this->levelName, 0) . $this->provider->getTeamName($this->levelName, 0) . "§f:" . $this->killCount[0] . "/" . $this->provider->getMaxKillCount($this->levelName) . " vs " . 
                                                              $this->provider->getTeamNameDecoration($this->levelName, 1) . $this->provider->getTeamName($this->levelName, 1) . "§f:" . $this->killCount[1] . "/" . $this->provider->getMaxKillCount($this->levelName));
                $this->plugin->getScheduler()->scheduleDelayedTask(new Callback([$this, 'ResultTask'], [$phase]), 40);
                return true;

            case 1:
                $winteam = $this->killCount[0] > $this->killCount[1] ? 0 : 1;
                $this->sendMessage("§aGAME>>§f" . $this->provider->getTeamNameDecoration($this->levelName, $winteam) . $this->provider->getTeamName($this->levelName, $winteam) . "§fチームの勝利!!");
                $this->plugin->discordManager->sendConvertedMessage('**❗`' . self::getName() . '`が終了しました 勝利チーム:' . $this->provider->getTeamName($this->levelName, $winteam) . ' **(' . date("m/d H:i") . ')', "game");
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
                $this->bossbar->setPercentage(($phase - 2) / 8);//2~10
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
        $this->applicants = [];
        $this->unloadLevel();
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

    public function unsetTeam($player)//要改善
    {
        foreach ($this->teamMembers as $teamKey => $teamMembers) 
        {
            foreach ($teamMembers as $memberKey => $member) {
                if($player->getName() === $member->getName())
                {
                    unset($this->teamMembers[$teamKey][$memberKey]);
                    return true;
                }
            }
        }

        return false;
    }

    public function isPlayer($player)
    {
        return $this->getTeam($player) !== false;
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

        if($this->killCount[$team] >= $this->provider->getMaxKillCount($this->levelName) && $this->TimeTableStatus === 1)
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
            $this->sendMessage("§aGAME>>§f" . $player->getNameTag() . "§fが" . $this->killstreak[$name] . "キルストリークを達成しました");
            $this->plugin->discordManager->sendConvertedMessage('**❗❗' . $player->getNameTag() . 'が' . $this->killstreak[$name] . 'キルストリークを達成しました**', "game");
            AccountProvider::get()->addExp($player, $this->killstreak[$name] * 5);
        }
    }

    public function resetKillStreak($player, $killer = null)
    {
        $name = $player->getName();
        if(isset($this->killstreak[$name]) && $this->killstreak[$name] >= 5 && $killer instanceof Player)
        {
            $this->sendMessage("§aGAME>>§f" . $killer->getNameTag() . "§fが" . $player->getNameTag() . "§fの" . $this->killstreak[$name] . "キルストリークを阻止しました");
            $this->plugin->discordManager->sendConvertedMessage('**❗❗' . $killer->getNameTag() . "§fが" . $player->getNameTag() . "§fの" . $this->killstreak[$name] . 'キルストリークを阻止しました**', "game");
        }

        unset($this->killstreak[$name]);
    }

    public function isGaming()
    {
        return $this->TimeTableStatus === 1;  
    }

    /*PMMPのアプデきたら処理変える*//*雑*/
    public function playSoundIndivudually($id, $pitch){
        foreach ($this->teamMembers as $team => $members) 
        {
            foreach ($members as $player) 
            {
                if($player->isOnline())
                {
                    $pk = new LevelEventPacket();
                    $pk->evid = $id;
                    $pk->position = $player->getPosition();
                    $pk->data = $pitch;
                    $player->dataPacket($pk);
                }
            }
        }
        foreach ($this->applicants as $applicant) 
        {
            $pk = new LevelEventPacket();
            $pk->evid = $id;
            $pk->position = $applicant->getPosition();
            $pk->data = $pitch;
            $applicant->dataPacket($pk);
        }
    }

    public function playSoundIndivudually_2($id){
        foreach ($this->teamMembers as $team => $members) 
        {
            foreach ($members as $player) 
            {
                if($player->isOnline())
                {
                    $pk = new LevelSoundEventPacket();
                    $pk->sound = $id;
                    $pk->position = $player->asVector3();
                    $player->dataPacket($pk);
                }
            }
        }
        foreach ($this->applicants as $applicant) 
        {
            $pk = new LevelSoundEventPacket();
            $pk->sound = $id;
            $pk->position = $applicant->asVector3();
            $applicant->dataPacket($pk);
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
            $tag = $player->isOp() ? AccountProvider::get()->getRankName($player) . " §b★§f" . $this->provider->getTeamNameDecoration($this->levelName, $team) . $player->getName() . '§f': 
                                     AccountProvider::get()->getRankName($player) . " " . $this->provider->getTeamNameDecoration($this->levelName, $team) . $player->getName() . '§f';
	    	$player->setNameTag($tag);
	    	$player->setDisplayName($tag);
            $player->setNameTagAlwaysVisible(false);
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
    	if($player->isOnline()) $this->plugin->playerManager->setDefaultNameTags($player);
    }

    public function setInventoryAll()
    {
        foreach ($this->teamMembers as $team => $members) 
        {
            foreach ($members as $player) 
            {
                $this->setInventory($player);
            }    
        }   
    }

    public function setInventory($player)
    {
        if(!$player->isOnline()) return true;

        $content = [];
        $content[] = $this->plugin->playerManager->getMainWeapon($player);
        $content = array_merge($content, $this->plugin->playerManager->getSubWeapons($player));
        $content[] = Item::get(262, 0, 1);
        $player->getInventory()->setContents($content);   
        
        $helmet = Item::get(298, 0, 1);
        $helmet->setCustomColor($this->provider->getTeamColor($this->levelName, $this->getTeam($player)));
        $player->getArmorInventory()->setHelmet($helmet);
    }

    public function setHealthAll()
    {
        foreach ($this->teamMembers as $team => $members) 
        {
            foreach ($members as $player) 
            {
                $this->setHealth($player);
            }    
        }   
    }

    public function setHealth($player)
    {
        if(!$player->isOnline()) return true;
        $player->setMaxHealth($this->provider->getHealth($this->levelName));
        $player->setHealth($this->provider->getHealth($this->levelName));
    }

    public function setDefaultHealthAll()
    {
        foreach ($this->teamMembers as $team => $members) 
        {
            foreach ($members as $player) 
            {
                if($player->isOnline()) $this->plugin->playerManager->setDefaultHealth($player);
            }    
        }      
    }

    public function sendTitle(string $title, string $subtitle = "", int $fadeIn = -1, int $stay = -1, int $fadeOut = -1)
    {
        foreach ($this->teamMembers as $team => $members) 
        {
            foreach ($members as $player) 
            {
                if($player->isOnline()) $player->addTitle($title, $subtitle, $fadeIn, $stay, $fadeOut);
            }    
        }   
    }

    public function sendMessage(string $message)
    {
        foreach ($this->teamMembers as $team => $members) 
        {
            foreach ($members as $player) 
            {
                if($player->isOnline()) $player->sendMessage($message);
            }    
        }   
    }

    public function sendTip(string $tip)
    {
        foreach ($this->teamMembers as $team => $members) 
        {
            foreach ($members as $player) 
            {
                if($player->isOnline()) $player->sendTip($tip);
            }    
        }   
    }
    
    /*賞金*/
    public function givePrizeAll()
    {
    	$winteam = $this->killCount[0] > $this->killCount[1] ? 0 : 1;
    	foreach ($this->teamMembers[$winteam] as $player) 
        {
        	AccountProvider::get()->addPoint($player, 2000);
            $player->sendMessage('§aGAME>>§f>>賞金を贈与しました');
        }
    }
    
    public function updateRanking()
    {
        Ranking::get()->Ranking();
    }

    public function onEventNPCTouch($event)
    {
        switch($event->getEventId())
        {

            case "game":
                $player = $event->getPlayer();
                if($this->isGaming()) $this->join($player);
                else
                {
                    if($this->isApplied($player)) $this->unapply($player);
                    else $this->apply($player);
                }
                break; 

            case "leave":
                $player = $event->getPlayer();
                $this->leave($player);
                break;
        }
    }

    public function onPlayerDeath($event)//雑
    {
        $player = $event->getPlayer();
        if($player->getLastDamageCause() instanceof EntityDamageByEntityEvent){
            $killer = $player->getLastDamageCause()->getDamager();
            $killerteam = $this->getTeam($killer);
            $playerteam = $this->getTeam($player);
            if($killerteam !== false && $playerteam !== false && $this->isGaming()){
                $item = Item::get(322, 0, 1);
                $killer->getInventory()->addItem($item);
                $this->addKillCount($killerteam);
                $this->addKillStreak($killer);
                $this->resetKillStreak($player, $killer);
                AccountProvider::get()->addPoint($killer, 100);
            }
            else
            {
                $this->resetKillStreak($player);
            }
        }
        else
        {
            $this->resetKillStreak($player);
        }
    }

    public function onDamage($event)//雑
    {
        if($event instanceof EntityDamageByEntityEvent)
        {
            $player = $event->getEntity();
            $atacker = $event->getDamager();
            if($player instanceof Player and $atacker instanceof Player){
                $playerteam = $this->getTeam($player);
                $atackerteam = $this->getTeam($atacker);
                if($playerteam === false || $atackerteam === false || $playerteam === $atackerteam || !$this->isGaming()){
                    $event->setCancelled(true);
                }
                else
                {
                    AccountProvider::get()->addExp($atacker, $event->getBaseDamage());
                }
            }
        }
    }

    public function onRespawn($event)
    {
        $player = $event->getPlayer();
        if($this->isPlayer($player))
        {
            $this->plugin->getScheduler()->scheduleDelayedTask(new Callback([$this, 'delayAddEffect'], [$player, new EffectInstance(Effect::getEffect(10), 20 * 7, 10, false)]), 1);
            $this->plugin->getScheduler()->scheduleDelayedTask(new Callback([$this, 'delayAddEffect'], [$player, new EffectInstance(Effect::getEffect(11), 20 * 7, 10, false)]), 1);
            $this->setInventory($player);
        }    
    }

    public function delayAddEffect($player, $effect)
    {
        $player->addEffect($effect);
    }

    public function onQuit($event)
    {
        $this->leave($event->getPlayer());
    }

    public function onArmorChange($event)//雑!!
    {
        $player = $event->getEntity();

        if(!$player instanceof Player) return true;

        if($this->isGaming() && $this->getTeam($player) !== false && $event->getSlot() === ArmorInventory::SLOT_HEAD && $event->getNewItem()->getId() === 0) $event->setCancelled(true);
    }

    public function loadLevel()
    {
        $this->levelName = $this->provider->getRandmonLevelName();
        $this->plugin->getServer()->loadLevel($this->levelName);
        $this->plugin->getServer()->getLevelByName($this->levelName)->setTime(14000);
        $this->plugin->getServer()->getLevelByName($this->levelName)->stopTime();
    }

    public function unloadLevel()
    {
        /*if(MainSettingProvider::get()->getLobbyWorldName() !== $this->levelName && TestFiringFieldProvider::get()->getWorldName() !== $this->levelName)
        {
            $level = $this->plugin->getServer()->getLevelByName($this->levelName);
            if(count($level->getPlayers()) < 1) $this->plugin->getServer()->unloadLevel($level);
        }*/
        $this->levelName = "";
    }
    
}
		
