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
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\item\Item;
use pocketmine\event\block\BlockBreakEvent;

class Main extends PluginBase implements Listener{

    public $db;
    public $skills;
    
    public function onEnable() {
         @mkdir($this->getDataFolder());
         $this->getLogger()->info("Skills Plugin Has Been Enabled!");
         $this->loadYml();
         //$this->getServer()->getPluginManager()->registerEvents(new Main($this), $this);
         $this->db = new \SQLite3($this->getDataFolder() . "Stats.db");
         $this->db->exec("CREATE TABLE IF NOT EXISTS skills (id INTEGER PRIMARY KEY AUTOINCREMENT, player TEXT, kill INTEGER, miner INTEGER, health INTEGER, death INTEGER);");
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
               $this->db->exec("INSERT INTO stats VALUES (NULL,'$playern','0','0','0','0')");
           }
           
           $sqlr1 = $this->db->query("SELECT COUNT(*) as count FROM skills WHERE player='$playern'");
           $sqla1 = $sqlr1->fetchArray();
           $multi1 = $sqla1["count"];
           if ($multi1 > 0){
               return ;           
           }else{
               $this->db->query("INSERT INTO skills VALUES (NULL,'$playern',0,0,0,0)");
           }
           }
        
        public function onPlayerDamage(EntityDamageEvent $event){
            $player = $event->getEntity();
            if ($player instanceof Player){
            $playern = $player->getName();
            $event->setDamage(50);
            $yml = (new Config($this->getServer()->getDataPath() . "/plugins/Skills/" . "Skill-Settings.yml", Config::YAML ,array()));
            $temp = $yml->getAll();
            $ymla = (new Config($this->getServer()->getDataPath() . "/plugins/Skills/" . "Block-Xp.yml", Config::YAML ,array()));
            $tempa = $yml->getAll();
            //$damage = $event->getDamage();
            //$this->getServer()->broadcastMessage($playern." HAPPENS--".$damage);
            }
        }
        
        public function CheckLevelUpKill(Player $player){
            //$this->getServer()->broadcastMessage(" DEBUG");
            $playern = $this->GetPlayerName($player);
            //$playern = $player->getName();
            $killsr = $this->db->query("SELECT * FROM stats WHERE player='$playern'");
            $killsa = $killsr->fetchArray();
            $kills = $killsa['kills'];
            $yml = (new Config($this->getServer()->getDataPath() . "/plugins/Skills/" . "Skill-Settings.yml", Config::YAML ,array()));
            $temp = $yml->getAll();
            $x = 0;
            $lastlevel = "";
            foreach ($temp['Killer-Levels'] as $key=>$val){
                $x = $x + 1;
                //$this->getServer()->broadcastMessage($playern."=".$kills." > ".$val."__".$x);
                if ($kills >= $val ){
                    $lastlevel = $key;
                }else{
                    break;
                }
            }
            $x = $x -1;
            //$this->getServer()->broadcastMessage(" LAST LEVEL--".$lastlevel);
            $sqlq = $this->db->query("SELECT * FROM skills WHERE player='$playern'");
            $sqla = $sqlq->fetchArray();
            if ($sqla['kill'] !== $x){
                $this->db->query("UPDATE skills SET kill='$x' WHERE player='$playern'");
                $this->getServer()->broadcastMessage($playern." Has Just Leveled Up!");
                $player->sendMessage("You Are Now Level ".$x. ", in Killing!");
                $player->sendMessage("You now do an extra ".$temp["Killer-Damage"]["Level".$x]." Of Damage!");

            }else{
                return true;
            }
            
            }
        public function CheckLevelUp(Player $p) {
            $this->CheckLevelUpKill($p);

        }
        
        public function OnBlockBreak(BlockBreakEvent $block){
        $placed = $this->getServer()->broadcastMessage($block->getBlock()->getId());
        $yml = (new Config($this->getServer()->getDataPath() . "/plugins/Skills/" . "block-names.yml", Config::YAML ,array()));
        $temp = $yml->getAll();
        $yml1 = (new Config($this->getServer()->getDataPath() . "/plugins/Skills/" . "block-xp.yml", Config::YAML ,array()));
        $temp1 = $yml1->getAll();
        if (isset($temp[$placed])){
            $o1 = $temp[$placed];
            $addxp = $temp1[$o1];
            $this->db->query("UPDATE skills SET blocksbroken=blocksbroken+'$addxp'");
        }
       
        }

        public function CheckLevelUpDeath(Player $player){
            //$this->getServer()->broadcastMessage(" DEBUG");
            $playern = $this->GetPlayerName($player);
            //$playern = $player->getName();
            $killsr = $this->db->query("SELECT * FROM stats WHERE player='$playern'");
            $killsa = $killsr->fetchArray();
            $kills = $killsa['deaths'];
            $yml = (new Config($this->getServer()->getDataPath() . "/plugins/Skills/" . "Skill-Settings.yml", Config::YAML ,array()));
            $temp = $yml->getAll();
            $x = 0;
            $lastlevel = "";
            foreach ($temp['Death-Levels'] as $key=>$val){
                $x = $x + 1;
                //$this->getServer()->broadcastMessage($playern."=".$kills." > ".$val."__".$x);
                if ($kills >= $val ){
                    $lastlevel = $key;
                }else{
                    break;
                }
            }
            $x = $x -1;
            //$this->getServer()->broadcastMessage(" LAST LEVEL--".$lastlevel);
            $sqlq = $this->db->query("SELECT * FROM skills WHERE player='$playern'");
            $sqla = $sqlq->fetchArray();
            if ($sqla['death'] !== $x){
                $this->db->query("UPDATE skills SET kill='$x' WHERE player='$playern'");
                $this->getServer()->broadcastMessage($playern." Has Just Leveled Up!");
                $player->sendMessage("You Are Now Level ".$x.", in Dieing!");
                $player->sendMessage("You now Get an Item Upon Respawning!");

            }else{
                return true;
            }
            
        }

        public function onPlayerDeath(PlayerDeathEvent $death){
            $killer = $death->getEntity()->getLastDamageCause()->getDamager();
            $player = $death->getEntity();
            if ($killer instanceof Plaeyr){
                $killern = $killer->getName();
                $this->db->query("UPDATE stats SET kills = kills + 1 WHERE player='".$killern."'");
            }
            $playern = $player->getName();
            ///$this->getServer()->broadcastMessage($player->getInventory());
            $this->db->query("UPDATE stats SET deaths = deaths + 1 WHERE player='".$playern."'");
            if ($killer instanceof Plaeyr){$this->CheckLevelUpKill($killer);}
            $this->CheckLevelUpDeath($player);
            //$this->db->q
            //Check if player Gets Upgrade            //Check if player Gets Upgrade
            //Check if player Gets Upgrade
            //Check if player Gets Upgrade
            //Check if player Gets Upgrade

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
                $this->getServer()->broadcastMessage($itemnum."-".$itemamount);
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
               /* 'Level10'=>'300',
                'Level9'=>'250',
                'Level8'=>'200',
                'Level7'=>'150',
                'Level6'=>'125',
                'Level5'=>'100',
                'Level4'=>'50',
                'Level3'=>'25',
                'Level2'=>'10',
                'Level1'=>'5'*/
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
            'allow-multi-bountys'=>true,
            'Current-Bounties' => array(),
        )))->getAll();
        $yml1 = (new Config($this->getServer()->getDataPath() . "/plugins/Skills/" . "block-names.yml", Config::YAML ,array(   
            '0'=>'Air',
            '1'=>'Stone',
            '2'=>'Grass Block',
            '3'=>'Dirt',
            '4'=>'Cobblestone',
            '5'=>'Wooden Plank',
            '6'=>'Sapling D',
            '7'=>'Bedrock',
            '8'=>'Water D',
            '9'=>'Stationary water D',
            '10'=>'Lava D',
            '11'=>'Stationary lava D',
            '12'=>'Sand',
            '13'=>'Gravel',
            '14'=>'Gold Ore',
            '15'=>'Iron Ore',
            '16'=>'Coal Ore',
            '17'=>'Wood D B',
            '18'=>'Leaves D B',
            '19'=>'Sponge',
            '20'=>'Glass',
            '21'=>'Lapis Lazuli Ore',
            '22'=>'Lapis Lazuli Block',
            '24'=>'Sandstone D',
            '26'=>'Bed D',
            '27'=>'Powered Rail',
            '30'=>'Cobweb',
            '31'=>'Tall Grass',
            '32'=>'Dead Bush',
            '35'=>'Wool D B',
            '37'=>'Dandelion',
            '38'=>'Poppy D B',
            '39'=>'Brown Mushroom',
            '40'=>'Red Mushroom',
            '41'=>'Gold Block',
            '42'=>'Iron Block',
            '43'=>'Double Stone SlabD',
            '44'=>'Stone SlabD',
            '45'=>'Brick Block',
            '46'=>'TNT',
            '47'=>'Bookshelf',
            '48'=>'Moss Stone',
            '49'=>'Obsidian',
            '50'=>'Torch',
            '51'=>'Fire',
            '52'=>'Monster Spawner T',
            '53'=>'Oak Wood Stairs D',
            '54'=>'Chest',
            '56'=>'Diamond Ore',
            '57'=>'Diamond Block',
            '58'=>'Crafting Table',
            '59'=>'Seeds D',
            '60'=>'Farmland D',
            '61'=>'Furnace D T',
            '62'=>'Burning Furnace D T',
            '63'=>'Sign Post D T I',
            '64'=>'Wooden Door D I',
            '65'=>'Ladder',
            '66'=>'Rail',
            '67'=>'Cobblestone Stairs D',
            '68'=>'Wall Sign D T I',
            '71'=>'Iron Door D',
            '73'=>'Redstone Ore',
            '74'=>'Glowing Redstone Ore',
            '78'=>'Snow Cover',
            '79'=>'Ice',
            '80'=>'Snow',
            '81'=>'Cactus',
            '82'=>'Clay',
            '83'=>'Sugar Cane',
            '85'=>'Fence',
            '86'=>'Pumpkin D',
            '87'=>'Netherrack',
            '89'=>'Glowstone',
            '91'=>'Jack o" Lantern D',
            '92'=>'Cake Block D',
            '95'=>'Invisible Bedrock',
            '96'=>'Trapdoor D',
            '98'=>'Stone Brick D B',
            '99'=>'Huge Brown Mushroom D I',
            '100'=>'Huge Red Mushroom D I',
            '101'=>'Iron Bars',
            '102'=>'Glass Pane',
            '103'=>'Melon',
            '104'=>'Pumpkin Stem D',
            '105'=>'Melon Stem D',
            '106'=>'Vines D',
            '107'=>'Fence Gate D',
            '108'=>'Brick Stairs D',
            '109'=>'Stone Brick Stairs D',
            '110'=>'Mycelium',
            '111'=>'Lily Pad',
            '112'=>'Nether Brick',
            '114'=>'Nether Brick Stairs D',
            '120'=>'End Portal Frame D',
            '121'=>'End Stone',
            '127'=>'Cocoa D I',
            '128'=>'Sandstone Stairs D',
            '129'=>'Emerald Ore',
            '133'=>'Block of Emerald',
            '134'=>'Spruce Wood Stairs D',
            '135'=>'Birch Wood Stairs D',
            '136'=>'Jungle Wood Stairs D',
            '139'=>'Cobblestone Wall',
            '141'=>'Carrots',
            '142'=>'Potato',
            '155'=>'Block of Quartz D',
            '156'=>'Quartz Stairs D',
            '157'=>'Wooden Double Slab D B',
            '158'=>'Wooden Slab D B',
            '159'=>'Stained Clay D B',
            '163'=>'Acacia Wood Stairs D',
            '164'=>'Dark Oak Wood Stairs D',
            '170'=>'Hay Block',
            '171'=>'Carpet D B',
            '172'=>'Hardened Clay',
            '173'=>'Block of Coal',
            '174'=>'Packed Ice',
            '243'=>'Podzol',
            '244'=>'Beetroot',
            '245'=>'Stone Cutter',
            '246'=>'Glowing Obsidian',
            '247'=>'Nether Reactor Core D')))->getAll();
        
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
            
        return true;
    }
    //Players Heath is 51 or 50...
        }