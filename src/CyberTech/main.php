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
         $this->getLogger()->info("Boutny Plugin Has Been Enabled!");
         $this->loadYml();
         //$this->getServer()->getPluginManager()->registerEvents(new Main($this), $this);
         $this->db = new \SQLite3($this->getDataFolder() . "Boutny.db");
         $this->db->exec("CREATE TABLE IF NOT EXISTS skills (id INTEGER PRIMARY KEY AUTOINCREMENT, player TEXT, kill TEXT, miner INTEGER, health TEXT);");
         $this->db->exec("CREATE TABLE IF NOT EXISTS stats (id INTEGER PRIMARY KEY AUTOINCREMENT , player TEXT, kills INTEGER, deaths INTEGER, blocksplased INTEGER, blocksbroken INTEGER);");
         $this->getServer()->getPluginManager()->registerEvents($this, $this);
         $this->api = EconomyAPI::getInstance();
         return true;
        }
        
         public function onCommand(CommandSender $sender, Command $command, $label, array $args){
        switch($command->getName()){
            case "skill":
                
            case "myskills":
                $this->GetPlayerSkills($sender->getName());
        }
        }
        
        public function onPlayerJoin(PlayerJoinEvent $player){
           $playern = $player->getPlayer()->getName();
           $sqlr = $this->db->query("SELECT COUNT(*) as count FROM stats WHERE player='$playern'");
           $sqla = $sqlr->fetchArray();
           $multi = $sqla["count"];
           if ($multi > 0){
               return TRUE;           
           }else{
               $this->db->query("INSERT INTO stats (id, player, kills, deaths, blocksplaced, blockbroken) VALUES (0,'$playern',0,0,0,0)");
           }
           
           $sqlr1 = $this->db->query("SELECT COUNT(*) as count FROM skills WHERE player='$playern'");
           $sqla1 = $sqlr1->fetchArray();
           $multi1 = $sqla1["count"];
           if ($multi1 > 0){
               return TRUE;           
           }else{
               $this->db->query("INSERT INTO skills (id, player, kill, miner, health) VALUES (0,'$playern',0,0,0,0)");
           }
           }


        public function onPlayerDeath(PlayerDeathEvent $death){
            $killer = $death->getEntity()->getLastDamageCause()->getEntity();
            $player = $death->getEntity();
            $death->getEntity()->getLastDamageCause()->getEntity()->getName();
            $killern = $killer->getName();
            $this->db->query("UPDATE stats WHERE player='$killern' SET kills = kills + 1");
        }
        
        public function GetPlayerSkills(){
           
        }
        }