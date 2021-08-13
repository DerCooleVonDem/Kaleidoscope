<?php

declare(strict_types=1);

namespace NexusMC\Kaleidoscope\creator;

use NexusMC\Kaleidoscope\editor\Edit;
use pocketmine\Player;

abstract class Creation{

    abstract function getName() : string;

    public function getClass() : Creation
    {
        return $this;
    }

    abstract function make() : Edit;

    abstract function parseArgs(Player $player, array $args) : bool;

    abstract function getUsage() : string;

}