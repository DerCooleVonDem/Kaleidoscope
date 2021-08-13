<?php

declare(strict_types=1);

namespace NexusMC\Kaleidoscope\creator;

use NexusMC\Kaleidoscope\editor\Edit;
use pocketmine\block\Air;
use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\utils\Random;

class Populate extends Creation{

    private ?Player $playerFrom = null;

    private ?Block $block = null;

    private ?int $radiusAroundTarget = null;

    private ?int $dentisy = null;

    /**
     * @return string
     */
    function getName(): string
    {
        return "Populate";
    }

    /**
     * @return Edit
     */
    function make(): Edit
    {

        $level = $this->playerFrom->getLevel();
        $target = $this->playerFrom->getTargetBlock(Level::Y_MAX);

        $edit = new Edit($level, true);

        for($i = 0; $i < $this->dentisy; ++$i){

            $pos = $this->getRandomPosInRadius($target->add(0,1), $this->radiusAroundTarget);

            $edit->addBlock($pos, $this->block->getId(), $this->block->getDamage());

        }

        return $edit;

    }

    /**
     * @param Player $player
     * @param array $args
     * @return bool
     */
    function parseArgs(Player $player, array $args): bool
    {
        $this->playerFrom = $player;

        if(empty($args[0]) or empty($args[1]) or empty($args[2])){
            return false;
        }

        try{

            if($block = Item::fromString($args[0])){
                $this->block = $block->getBlock();
            }

        }catch (\InvalidArgumentException $exception){

            return false;

        }

        if(is_int($args[1])){
            $this->radiusAroundTarget = $args[1];
        }

        if(is_int($args[2])){
            $this->dentisy = $args[2];
        }

        return true;
    }

    /**
     * @return string
     */
    function getUsage(): string
    {
        return "<BlockName> <Radus> <Dentisy>";
    }


    function getRandomPosInRadius(Vector3 $mid, int $radius) : Vector3{

        $random = mt_rand(0, mt_getrandmax() - 1) / mt_getrandmax();

        $a = $random * 2 * M_1_PI;
        $r = $radius * sqrt($random);

        $x = (int)$r * cos($a)+$mid->x;
        $z = (int)$r * sin($a)+$mid->z;
        $y = $this->playerFrom->getLevel()->getChunk($x, $z)->getHighestBlockAt($x >> 4, $z >> 4)+1;

        return new Vector3($x,$y,$z);
    }
}