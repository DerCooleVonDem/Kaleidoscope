<?php

declare(strict_types=1);

namespace NexusMC\Kaleidoscope\commands;

use NexusMC\Kaleidoscope\creator\Creation;
use NexusMC\Kaleidoscope\creator\CreationFactory;
use NexusMC\Kaleidoscope\editor\Editor;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class EditCommand extends Command{

    public function getUsage(): string
    {
        return "Usage: /edit <Creation> <Args>";
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param string[] $args
     * @return mixed
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if($sender instanceof Player){

            if(empty($args[0])) return;

            $name = $args[0];

            $creation = CreationFactory::getCreation($name);

            if(!is_null($creation) && $creation instanceof Creation){

                array_shift($args);

                $result = $creation->parseArgs($sender, $args);

                if(!$result){

                    $creationUsage = $creation->getUsage();

                    $sender->sendMessage("Usage: /edit {$name} {$creationUsage}");

                    return;

                }

                $edit = $creation->make();

                Editor::edit($edit);

            }
        }
    }

}