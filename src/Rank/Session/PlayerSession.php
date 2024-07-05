<?php

namespace Rank\Session;

use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use Rank\Loader;
use Rank\RankManager\RankManager;

class PlayerSession
{

    public Player $player;

    public function __construct(Player $player)
    {
        $this->player = $player;
    }


    public function load(): void
    {
        if(!file_exists(Loader::getInstance()->getDataFolder().'players/'.strtolower($this->player->getName()).'.yml')){
            $playerConfig = new Config(Loader::getInstance()->getDataFolder().'players/'.strtolower($this->player->getName()).'.yml');
            $playerConfig->set('ranks',[]);
            $playerConfig->save();
        }
        $Config = new Config(Loader::getInstance()->getDataFolder().'players/'.strtolower($this->player->getName()).'.yml');
        $this->updatePlayerNameTag($this->player, $Config->get('ranks'));
        $this->updatePlayerPermission($this->player, $Config->get('ranks'));
    }

    private function updatePlayerNameTag(Player $player, array $get): void
    {
        $nametag = "";
        foreach ($get as $item) {
            if(isset(RankManager::$ranks[$item])){
                 $prefix = RankManager::$ranks[$item]['prefix'];
                 $nametag .= " ". $prefix;

            }
        }
        $nametag .=  ' '.TextFormat::WHITE.$player->getName();
        $player->setNameTag($nametag);
        $player->setDisplayName($nametag);
    }

    public function getRanks()
    {
        $playerConfig = new Config(Loader::getInstance()->getDataFolder().'players/'.strtolower($this->player->getName()).'.yml');
        return $playerConfig->get('ranks', []);
    }
    public function setRank(array $rank): void
    {
        $playerConfig = new Config(Loader::getInstance()->getDataFolder().'players/'.strtolower($this->player->getName()).'.yml');
        $playerConfig->set('ranks',$rank);
        $playerConfig->save();
    }

    private function updatePlayerPermission(Player $player, array $get): void
    {
        $player->recalculatePermissions();
            foreach ($get as $item) {
                if(isset(RankManager::$ranks[$item])){
                    $permissions = RankManager::$ranks[$item]['permissions'];
                    foreach ($permissions as $permission) {
                        $player->addAttachment(Loader::getInstance())->setPermission($permission, true);
                    }
                }
            }
    }
}