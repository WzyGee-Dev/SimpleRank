<?php

namespace Rank\RankManager;

use pocketmine\utils\Config;
use Rank\Loader;

class RankManager
{

    public static array $ranks = [];

    public static function loadRanks(): void
    {
        self::$ranks = (new Config(Loader::getInstance()->getDataFolder().'ranks.yml',Config::YAML))->get('ranks', []);
    }
}