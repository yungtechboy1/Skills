<?php
/*
 * KillBounty (v1.0.0.0) by CyberTech++
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
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\item\Item;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;

class Main extends PluginBase implements Listener{

    public $db;
    public $skills;
    public $econ;
    
    public function onEnable() {
         @mkdir($this->getDataFolder());
         $this->getLogger()->info("Skills Plugin Has Been Enabled!");
         $this->loadYml();
         //$this->getServer()->getPluginManager()->registerEvents(new Main($this), $this);
         $this->db = new \SQLite3($this->getDataFolder() . "Stats.db");
         $this->db->exec("CREATE TABLE IF NOT EXISTS skills (id INTEGER PRIMARY KEY AUTOINCREMENT, player TEXT, kill INTEGER, miner INTEGER, health INTEGER, death INTEGER, points INTEGER);");
         $this->db->exec("CREATE TABLE IF NOT EXISTS stats (id INTEGER PRIMARY KEY AUTOINCREMENT , player TEXT, kills INTEGER, deaths INTEGER, blocksplaced INTEGER, blocksbroken INTEGER,points INTEGER;);");
         $this->getServer()->getPluginManager()->registerEvents($this, $this);
         $this->econ = EconomyAPI::getInstance();
         return true;
        }
        
         public function onCommand(CommandSender $sender, Command $command, $label, array $args){
        switch($command->getName()){
            case "skill":
                if ($args[0] == "trade"){
                    $yml3 = (new Config($this->getServer()->getDataPath() . "/plugins/Skills/" . "block-shop.yml", Config::YAML ,array()));
                    $temp = $yml3->getAll();
                    if (!isset($args[1])){
                        $sender->sendMessage("Invalid Useage");
                        $sender->sendMessage("/skill trade <amount> or /skill trade info");
                    }elseif($args[0] == "info"){
                        $sender->sendMessage("The Current Trade Rate is 1XP for $".$temp['Money-Exchange']);
                    }else{
                        $playern = $sender->getName();
                        $xpq = $this->db->query("SELECT * FROM stats WHERE player='$playern'");
                        $xpa = $xpq->fetchArray();
                        $xp = $xpa['blocksbroken'];
                        if ($args[1] > $xp){
                            $sender->sendMessage("Uh Oh! You Dont Have Enough XP!");
                        }  else {
                            $money = (($temp['Money-Exchange']*1)*($xp*1));
                            $this->api->addMoney ( $sender->getName(), $money );
                            $this->db->query("UPDATE stats SET blocksbroken=blocksbroken-'$xp'");
                        }
                    }
                }elseif(args[0] == "levelup"){
                    if ($sender instanceof Player){
                        $yml = (new Config($this->getServer()->getDataPath() . "/plugins/Skills/" . "Skill-Settings.yml", Config::YAML ,array()));
                        $temp = $yml->getAll();
                        $playern = $sender->getName();
                        $xpq = $this->db->query("SELECT * FROM stats WHERE player='$playern'")->fetchArray();
                        $xp = $xpq['points'];
                        $pp = $this->db->query("SELECT * FROM skills WHERE player='$playern'")->fetchArray();
                        $sender->sendMessage("You Have ".$xp." XP Points");
                        $sender->sendMessage("--------Skill-Shop--------");
                        $knl = $pp['kill'] + 1;
                        
                        $sender->sendMessage("Upgrade Killer Level for ".$pp['kill']);
                        $sender->sendMessage("Death Level:".$pp['death']);
                        $sender->sendMessage("Miner Level:".$pp['miner']);
                        $sender->sendMessage("Health Level:".$pp['health']);
                        
                    }
                }
                
            case "myskills":
                if ($sender instanceof Player){
                    $pp = $this->db->query("SELECT * FROM skills WHERE player='$playern'")->fetchArray();
                    $sender->sendMessage("--------Your-Skills--------");
                            $sender->sendMessage("Killer Level: ".$pp['kill']);
                            $sender->sendMessage("Death Level: ".$pp['death']);
                            $sender->sendMessage("Miner Level: ".$pp['miner']);
                            $sender->sendMessage("Health Level: ".$pp['health']);
                    //$this->GetPlayerSkills($sender->getName());
                }
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
               $this->db->exec("INSERT INTO stats VALUES (NULL,'$playern','0','0','0','0','0')");
           }
           
           $sqlr1 = $this->db->query("SELECT COUNT(*) as count FROM skills WHERE player='$playern'");
           $sqla1 = $sqlr1->fetchArray();
           $multi1 = $sqla1["count"];
           if ($multi1 > 0){
               return ;           
           }else{
               $this->db->query("INSERT INTO skills VALUES (NULL,'$playern',0,0,0,0,0)");
           }
           }
        
       /* public function onPlayerDamage(EntityDamageEvent $event){
            $player = $event->getEntity();
            if ($player instanceof Player){
            $playern = $player->getName();
            $event->setDamage(50);
            
            //$damage = $event->getDamage();
            //$this->getServer()->broadcastMessage($playern." HAPPENS--".$damage);
            }
        }*/
            
        public function CheckLevelUp(Player $p) {
            $this->CheckLevelUpKill($p);

        }
        
        public function OnBlockBreak(BlockBreakEvent $block){
            $placed = $block->getBlock()->getId();
            $yml = (new Config($this->getServer()->getDataPath() . "/plugins/Skills/" . "block-xp.yml", Config::YAML ,array()));
            $temp = $yml->getAll();
            $this->db->query("UPDATE stats SET blocksbroken=blocksbroken+1 WHERE player='$playern'");
            if (isset($temp['break'][$placed])){
                $xp = $temp['break'][$placed];
                $this->db->query("UPDATE stats SET points=points+'$xp' WHERE player='$playern'");

            }else{
                $xp = $temp['break']['default'];
                $this->db->query("UPDATE stats SET points=points+'$xp' WHERE player='$playern'");
            }
       
        }
        
        public function onBlockPlace(BlockPlaceEvent $block) {
            $placed = $block->getBlock()->getId();
            $yml = (new Config($this->getServer()->getDataPath() . "/plugins/Skills/" . "block-xp.yml", Config::YAML ,array()));
            $temp = $yml->getAll();
            $this->db->query("UPDATE stats SET blocksbroken=blocksbroken+1 WHERE player='$playern'");
            if (isset($temp['place'][$placed])){
                $xp = $temp['place'][$placed];
                $this->db->query("UPDATE stats SET points=points+'$xp' WHERE player='$playern'");

            }else{
                $xp = $temp['place']['default'];
                $this->db->query("UPDATE stats SET points=points+'$xp' WHERE player='$playern'");
            }
        }
        
        public function onPlayerDeath(PlayerDeathEvent $death){
             if ($death->getEntity()->getLastDamageCause()->getCause() == 1){
                $killer = $death->getEntity()->getLastDamageCause()->getDamager();
                $player = $death->getEntity();
                $playern = $player->getName();
                if ($killer instanceof Player){
                    $killern = $killer->getName();
                    $this->db->query("UPDATE stats SET kills = kills + 1 WHERE player='".$killern."'");
                    $this->db->query("UPDATE stats SET points = points + 1 WHERE player='".$killern."'");
                    $killer->sendMessage("You Just Got 1 Point For Killing ".$playern);
                }

                ///$this->getServer()->broadcastMessage($player->getInventory());
                $this->db->query("UPDATE stats SET deaths = deaths + 1 WHERE player='".$playern."'");
                $this->db->query("UPDATE stats SET points = points - 2 WHERE player='".$playern."'");
             }else{
                $player = $death->getEntity();
                $playern = $player->getName();
                $this->db->query("UPDATE stats SET deaths = deaths + 1 WHERE player='".$playern."'");
                $this->db->query("UPDATE stats SET points = points - 2 WHERE player='".$playern."'");
                 
             }
        }
        
        public function onPlayerRespawn(PlayerRespawnEvent $respawn){
            $yml = (new Config($this->getServer()->getDataPath() . "/plugins/Skills/" . "Skill-Settings.yml", Config::YAML ,array()));
            $temp = $yml->getAll();
            $player = $respawn->getPlayer();
            $playern = $respawn->getPlayer()->getName();
            $sqlq = $this->db->query("SELECT * FROM skills WHERE player='".$playern."'");
            $sqla = $sqlq->fetchArray();
            $dielevel = $sqla['kill'];
            if ($dielevel == 0){
                
            }else{
                $titem = explode(":",$temp['Death-Reward']['Level'.$dielevel]);
                $itemnum = $titem[0];
                $itemamount = $titem[1];
                //$this->getServer()->broadcastMessage($itemnum."-".$itemamount);
                $respawn->getPlayer()->getInventory()->addItem(new Item($itemnum,0,$itemamount));
                
            }
        }


        public function GetPlayerSkills(){
            
           return true;
        }
        
        public function GetPlayerName(Player $p) {
            return $p->getName();
        }
        
        public function loadYml(){
        @mkdir($this->getServer()->getDataPath() . "/plugins/Skills/");
        $this->skills = (new Config($this->getServer()->getDataPath() . "/plugins/Skills/" . "Skill-Settings.yml", Config::YAML ,array(
            'Killer-Levels'=>array(
                'Level1'=>'5',
                'Level2'=>'10',
                'Level3'=>'25',
                'Level4'=>'50',
                'Level5'=>'100',
                'Level6'=>'125',
                'Level7'=>'150',
                'Level8'=>'200',
                'Level9'=>'250',
                'Level10'=>'300'
            ),
            'Killer-Damage'=>array(
                'Level1'=>'.1',
                'Level2'=>'.2',
                'Level3'=>'.3',
                'Level4'=>'.5',
                'Level5'=>'1',
                'Level6'=>'2',
                'Level7'=>'3',
                'Level8'=>'5',
                'Level9'=>'10',
                'Level10'=>'15'
            ),
            'Death-Levels'=>array(
                'Level1'=>'5',
                'Level2'=>'10',
                'Level3'=>'25',
                'Level4'=>'50',
                'Level5'=>'100',
                'Level6'=>'125',
                'Level7'=>'150',
                'Level8'=>'200',
                'Level9'=>'250',
                'Level10'=>'300'
            ),
            'Death-Reward'=>array(
                //Gives Item To Restart With
                'Level1'=>'265:1',
                'Level2'=>'268:1',
                'Level3'=>'274:1',
                'Level4'=>'272:1',
                'Level5'=>'303:1',
                'Level6'=>'304:1',
                'Level7'=>'307:1',
                'Level8'=>'308:1',
                'Level9'=>'265:1',
                'Level10'=>'264:1'  
            ),
            'Heal-Levels'=>array(
                'Level1'=>'1',
                'Level2'=>'2',
                'Level3'=>'3',
                'Level4'=>'5',
                'Level5'=>'10',
                'Level6'=>'15',
                'Level7'=>'20',
                'Level8'=>'25',
                'Level9'=>'30',
                'Level10'=>'35'
            )
        )))->getAll();
        (new Config($this->getServer()->getDataPath() . "/plugins/Skills/" . "block-xp.yml", Config::YAML ,array(   
            'break'=>array(
                '14'=>10,
                '15'=>8,
                '16'=>5,
                '56'=>50,
                '129'=>100,
                'default'=>.01
                ),
            'place'=>array(
                'default'=>0
            )
            )))->getAll();
        /*
        $yml2 = (new Config($this->getServer()->getDataPath() . "/plugins/Skills/" . "block-xp.yml", Config::YAML ,array(
            'Air'=>'1',
            'Stone'=>'1',
            'Grass Block'=>'1',
            'Dirt'=>'1',
            'Cobblestone'=>'1',
            'Wooden Plank'=>'1',
            'Sapling D'=>'1',
            'Bedrock'=>'1',
            'Water D'=>'1',
            'Stationary water D'=>'1',
            'Lava D'=>'1',
            'Stationary lava D'=>'1',
            'Sand'=>'1',
            'Gravel'=>'1',
            'Gold Ore'=>'1',
            'Iron Ore'=>'1',
            'Coal Ore'=>'1',
            'Wood D B'=>'1',
            'Leaves D B'=>'1',
            'Sponge'=>'1',
            'Glass'=>'1',
            'Lapis Lazuli Ore'=>'1',
            'Lapis Lazuli Block'=>'1',
            'Sandstone D'=>'1',
            'Bed D'=>'1',
            'Powered Rail'=>'1',
            'Cobweb'=>'1',
            'Tall Grass'=>'1',
            'Dead Bush'=>'1',
            'Wool D B'=>'1',
            'Dandelion'=>'1',
            'Poppy D B'=>'1',
            'Brown Mushroom'=>'1',
            'Red Mushroom'=>'1',
            'Gold Block'=>'1',
            'Iron Block'=>'1',
            'Double Stone SlabD'=>'1',
            'Stone SlabD'=>'1',
            'Brick Block'=>'1',
            'TNT'=>'1',
            'Bookshelf'=>'1',
            'Moss Stone'=>'1',
            'Obsidian'=>'1',
            'Torch'=>'1',
            'Fire'=>'1',
            'Monster Spawner T'=>'1',
            'Oak Wood Stairs D'=>'1',
            'Chest'=>'1',
            'Diamond Ore'=>'1',
            'Diamond Block'=>'1',
            'Crafting Table'=>'1',
            'Seeds D'=>'1',
            'Farmland D'=>'1',
            'Furnace D T'=>'1',
            'Burning Furnace D T'=>'1',
            'Sign Post D T I'=>'1',
            'Wooden Door D I'=>'1',
            'Ladder'=>'1',
            'Rail'=>'1',
            'Cobblestone Stairs D'=>'1',
            'Wall Sign D T I'=>'1',
            'Iron Door D'=>'1',
            'Redstone Ore'=>'1',
            'Glowing Redstone Ore'=>'1',
            'Snow Cover'=>'1',
            'Ice'=>'1',
            'Snow'=>'1',
            'Cactus'=>'1',
            'Clay'=>'1',
            'Sugar Cane'=>'1',
            'Fence'=>'1',
            'Pumpkin D'=>'1',
            'Netherrack'=>'1',
            'Glowstone'=>'1',
            'Jack o" Lantern D'=>'1',
            'Cake Block D'=>'1',
            'Invisible Bedrock'=>'1',
            'Trapdoor D'=>'1',
            'Stone Brick D B'=>'1',
            'Huge Brown Mushroom D I'=>'1',
            'Huge Red Mushroom D I'=>'1',
            'Iron Bars'=>'1',
            'Glass Pane'=>'1',
            'Melon'=>'1',
            'Pumpkin Stem D'=>'1',
            'Melon Stem D'=>'1',
            'Vines D'=>'1',
            'Fence Gate D'=>'1',
            'Brick Stairs D'=>'1',
            'Stone Brick Stairs D'=>'1',
            'Mycelium'=>'1',
            'Lily Pad'=>'1',
            'Nether Brick'=>'1',
            'Nether Brick Stairs D'=>'1',
            'End Portal Frame D'=>'1',
            'End Stone'=>'1',
            'Cocoa D I'=>'1',
            'Sandstone Stairs D'=>'1',
            'Emerald Ore'=>'1',
            'Block of Emerald'=>'1',
            'Spruce Wood Stairs D'=>'1',
            'Birch Wood Stairs D'=>'1',
            'Jungle Wood Stairs D'=>'1',
            'Cobblestone Wall'=>'1',
            'Carrots'=>'1',
            'Potato'=>'1',
            'Block of Quartz D'=>'1',
            'Quartz Stairs D'=>'1',
            'Wooden Double Slab D B'=>'1',
            'Wooden Slab D B'=>'1',
            'Stained Clay D B'=>'1',
            'Acacia Wood Stairs D'=>'1',
            'Dark Oak Wood Stairs D'=>'1',
            'Hay Block'=>'1',
            'Carpet D B'=>'1',
            'Hardened Clay'=>'1',
            'Block of Coal'=>'1',
            'Packed Ice'=>'1',
            'Podzol'=>'1',
            'Beetroot'=>'1',
            'Stone Cutter'=>'1',
            'Glowing Obsidian'=>'1',
            'Nether Reactor Core D'=>'1'
        )))->getAll();
        
        $yml3 = (new Config($this->getServer()->getDataPath() . "/plugins/Skills/" . "block-shop.yml", Config::YAML ,array(
            'Money-Exchange'=>'1'
            )))->getAll();*/
            
        return true;
    }
    //Players Heath is 51 or 50...
        }