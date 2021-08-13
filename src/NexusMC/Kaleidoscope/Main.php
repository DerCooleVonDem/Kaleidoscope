<?php

declare(strict_types=1);

namespace NexusMC\Kaleidoscope;

use NexusMC\Kaleidoscope\commands\EditCommand;
use NexusMC\Kaleidoscope\creator\CreationFactory;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase{


    public function onEnable()
    {
        $this->getServer()->getCommandMap()->register("edit", new EditCommand("edit", "Simple Editor"));

        CreationFactory::registerDefaults();

    }



}
