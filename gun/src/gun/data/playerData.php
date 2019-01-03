<?php
namespace gun\data;

use gun\scoreboard\scoreboard;

class playerData{
        
        private static $instance;

       	public function __construct($plugin){
		$this->plugin = $plugin;
        	$this->DataFolder = $plugin->getDatafolder();
            	self::$instance = $this;
            	$this->CreateConfig();
        }

        public static function init(){
		if(is_null(self::$api)){
			self::$api = new self;
            	}
        }
        
    	public function closeDB(){
    		$this->DB->close();
    	}

        public function CreateConfig(){
    	    	$file = $this->DataFolder."playerdata.db";
            	if(!file_exists($file)){
            		$this->DB = new \SQLite3($file, SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
            	}else{
            		$this->DB = new \SQLite3($file, SQLITE3_OPEN_READWRITE);
            	}
            	$this->DB->query("CREATE TABLE IF NOT EXISTS player (name TEXT PRIMARY KEY, exp INT, kill INT, death INT, money INT)");
            	return true;
        }

        public function createAccount($username){
    		$this->DB->query("INSERT OR REPLACE INTO player VALUES(\"$username\", \"0\",  \"0\", \"0\", \"0\")");
    		return true;
        }

        public function getAccount($username){
        	$result = $this->DB->query("SELECT * FROM player WHERE name = \"$username\""); 
        	return $result->fetchArray();
        }
	
        public function setAccount($username,$name,$data = false){
    		switch($name){
    			case "exp":
    				$this->DB->query("UPDATE player SET exp =   \"$data\"  WHERE name = \"$username\"");
    				break;
    			case "kill":
    				$this->DB->query("UPDATE player SET kill =   \"$data\"  WHERE name = \"$username\"");
    				break;
    			case "death":
    				$this->DB->query("UPDATE player SET death =   \"$data\"  WHERE name = \"$username\"");
    				break;
    			case "money":
    				$this->DB->query("UPDATE player SET money =   \"$data\"  WHERE name = \"$username\"");
    				break;
    		}
    		/*scoreboardを連携して動かす*/
    		scoreboard::getScoreBoard()->updateScoreBoard($name, $data, $this->plugin->getServer()->getPlayer($username));
        }
        
        public static function getPlayerData(){
        	return self::$instance;
        }
}
