<?php

declare(strict_types=1);

namespace EnteFan;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\item\ItemFactory;

use muqsit\invmenu\InvMenu;
use muqsit\invmenu\InvMenuHandler;
use muqsit\invmenu\inventory\InvMenuInventory;

class Main extends PluginBase{
    
    public function onEnable():void{
        if(!InvMenuHandler::isRegistered()){
            InvMenuHandler::register($this);
        }
        $this->getLogger()->info("§aBACKPACK enabled");
    }
    
    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args):bool{
        switch($cmd->getName()){
            case "backpack":
                $this->openBackPack($sender);
            break;
        }
        return true;
    }
    
    public function openBackPack(Player $player){
        $name = $player->getName();
        $content = null;
        if(file_exists($this->getDataFolder()."$name.yml")){
            $config = new Config($this->getDataFolder()."$name.yml", Config::YAML);
            $value = $config->get($name);
            $content = json_decode(base64_decode($value), true);
        }
        $menu = InvMenu::create(InvMenu::TYPE_CHEST);
        $menu->setName("BackPack");
        $inv = $menu->getInventory();
        if(!$content == null){
            $this->setContentNotNull($inv, $content);
        }
        $menu->setInventoryCloseListener(function (Player $player, InvMenuInventory $inventory) : void{
            $contents = $inventory->getContents();
            if($contents == null){
                return;
            }
            $json_encode = json_encode($contents);
            $base64 = base64_encode($json_encode);
            $name = $player->getName();
            $config = new Config($this->getDataFolder()."$name.yml", Config::YAML);
            $config->set($name, $base64);
            $config->save();
        });
        $menu->send($player);
    }
    
    public function setContentNotNull($inv, array $content) : void{
        $factory = new ItemFactory();
        foreach ($content as $slot => $data){
            $id = $data["id"];
            $count = $data["count"] ?? 1;
            $inv->setItem($slot, $factory->get($id, 0, $count));
        }
    }
}
