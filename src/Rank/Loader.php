<?php
declare(strict_types=1);

namespace Rank;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\chat\ChatFormatter;
use pocketmine\player\chat\LegacyRawChatFormatter;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use Rank\RankManager\RankManager;
use Rank\Session\SessionManager;

class Loader extends PluginBase implements Listener
{

    public static Loader $instance;

    public static function getInstance(): Loader
    {
        return self::$instance;
    }

    public function onEnable(): void
    {
        $this->saveResource('ranks.yml');
        self::$instance = $this;
        RankManager::loadRanks();
        if(!is_dir($this->getDataFolder().'players/')){
            mkdir($this->getDataFolder().'players/', 0777, true);
        }
       $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onPlayerJoin(PlayerJoinEvent $event)
    {
        $player = $event->getPlayer();
        SessionManager::createSession($player);
        SessionManager::getSessions($player)->load();
    }
    public function onQuit(PlayerQuitEvent $event)
    {
        $player = $event->getPlayer();
        SessionManager::remove($player);
    }

    public function onChat(PlayerChatEvent $event)
    {
        $player = $event->getPlayer();
        $message = $event->getMessage();
        $color = "";
        foreach (SessionManager::getSessions($player)->getRanks() as $rank) {
            if(isset(RankManager::$ranks[$rank])){
                $color = RankManager::$ranks[$rank]['colorChat'];
            }
        }
       $event->setFormatter(new LegacyRawChatFormatter($player->getNameTag(). $color.' : '.$message));
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
            if ($command->getName() === "setrank") {
                if (isset($args[0]) && isset($args[1])) {
                    $playerName = $this->getServer()->getPlayerExact($args[0]);
                    $rank = $args[1];
                    $currentRank = SessionManager::getSessions($playerName)->getRanks();
                    if (isset(RankManager::$ranks[$rank])) {
                        if (!in_array($rank, $currentRank)) {
                            $currentRank[] = $rank;
                            SessionManager::getSessions($playerName)->setRank($currentRank);
                            SessionManager::getSessions($playerName)->load();
                            $playerName->sendMessage('tienes un nuevo rank: ' . $rank);
                        } else {
                            $sender->sendMessage('ya tienes ese rango');
                        }
                        return true;
                    } else {
                        $sender->sendMessage('rank no existe');
                        return false;
                    }
                } else {
                    $sender->sendMessage('use /setrank <player> <rank>');
                }
            }
            if ($command->getName() === "removerank") {
                if (isset($args[0]) && isset($args[1])) {
                    $playerName = $this->getServer()->getPlayerExact($args[0]);
                        $currentRank = SessionManager::getSessions($playerName)->getRanks();
                    $rank = $args[1];
                    if (isset(RankManager::$ranks[$rank])) {
                        if (in_array($rank, $currentRank)) {
                            $currentRank = array_diff($currentRank, [$rank]);
                            SessionManager::getSessions($playerName)->setRank($currentRank);
                            SessionManager::getSessions($playerName)->load();
                            $playerName->sendMessage("te han quidado el rango: " . $rank);
                        } else {
                            $sender->sendMessage('ya no tiene ese rango');
                        }
                    } else {
                        $sender->sendMessage('rank no existe');
                        return false;
                    }
                } else {
                    $sender->sendMessage('use /removerank <player> <rank>');
                }
            }
        return true;
    }
}