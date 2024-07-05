<?php

namespace Rank\Session;

use pocketmine\player\Player;

class SessionManager
{


    private static array $sessions = [];

    public static function createSession(Player $player): void
    {
        self::$sessions[$player->getName()] = new PlayerSession($player);
    }

    public static function getSessions(Player $player): ? PlayerSession
    {
        return self::$sessions[$player->getName()] ?? null;
    }

    public static function remove(Player $player): void
    {
        unset(self::$sessions[$player->getName()]);
    }
}