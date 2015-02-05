<?php
/*
 * KillBounty (v1.0.1.1) by CyberTech++
 * Developer: CyeberTech++ (Yungtechboy1)
 * Website: http://www.cybertechpp.com
 * Date: 2/3/2015 11:47 PM (UTC)
 * Copyright & License: (C) 2015 CyberTech++
 */

namespace CyberTech;

use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use onebone\economyapi\EconomyAPI;
use pocketmine\event\player\PlayerJoinEvent;

class Main extends PluginBase implements Listener{

    public $db;
    
    public function onEnable() {
         @mkdir($this->getDataFolder());
         $this->getLogger()->info("Skills Plugin Has Been Enabled!");
         //$this->loadYml();
         //$this->getServer()->getPluginManager()->registerEvents(new Main($this), $this);
         $this->db = new \SQLite3($this->getDataFolder() . "Stats.db");
         $this->db->exec("CREATE TABLE IF NOT EXISTS skills (id INTEGER PRIMARY KEY AUTOINCREMENT, player TEXT, kill INTEGER, miner INTEGER, health INTEGER);");
         $this->db->exec("CREATE TABLE IF NOT EXISTS stats (id INTEGER PRIMARY KEY AUTOINCREMENT , player TEXT, kills INTEGER, deaths INTEGER, blocksplaced INTEGER, blocksbroken INTEGER);");
         $this->getServer()->getPluginManager()->registerEvents($this, $this);
         //$this->api = EconomyAPI::getInstance();
         return true;
        }
        
         public function onCommand(CommandSender $sender, Command $command, $label, array $args){
        switch($command->getName()){
            case "skill":
                
            case "myskills":
                //$this->GetPlayerSkills($sender->getName());
        }
        }
        
        public function onPlayerJoin(PlayerJoinEvent $player){
           $playern = $player->getPlayer()->getName();
           $sqlr = $this->db->query("SELECT COUNT(*) as count FROM stats WHERE player='$playern'");
           $sqla = $sqlr->fetchArray();
           $multi = $sqla["count"];
           if ($multi > 0){
               return ;           
           }else{
               $this->db->exec("INSERT INTO stats VALUES ('0','$playern','0','0','0','0')");
           }
           
           $sqlr1 = $this->db->query("SELECT COUNT(*) as count FROM skills WHERE player='$playern'");
           $sqla1 = $sqlr1->fetchArray();
           $multi1 = $sqla1["count"];
           if ($multi1 > 0){
               return ;           
           }else{
               $this->db->query("INSERT INTO skills (id, player, kill, miner, health) VALUES (0,'$playern',0,0,0,0)");
           }
           }
        
        public function onPlayerDamage(EntityDamageEvent $event){
            $player = $event->getEntity();
            $playern = $player->getName();
            $this->getServer()->broadcastMessage($playern." HAPPENS");
            
        }

        public function onPlayerDeath(PlayerDeathEvent $death){
            $killer = $death->getEntity()->getLastDamageCause()->getDamager();
            $player = $death->getEntity();
            $death->getEntity()->getLastDamageCause()->getEntity()->getName();
            //$killern = GetPlayerName($killer);
            //$playern = GetPlayerName($player);
            $this->getServer()->broadcastMessage($player->getName()." HAPPENS ".$killer->getName());
            $this->db->exec("UPDATE stats SET kills = kills + 1 WHERE player='$killern'");
            $this->db->exec("UPDATE stats SET deaths = deaths + 1 WHERE player='$playern'");
            //$this->db->q
            //Check if player Gets Upgrade
        }
        
        public function GetPlayerSkills(){
           return true;
        }
        
        public function GetPlayerName(Player $p) {
            return $p->getName();
        }
        }