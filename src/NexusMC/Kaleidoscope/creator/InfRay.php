<?php

declare(strict_types=1);

namespace NexusMC\Kaleidoscope\creator;

use NexusMC\Kaleidoscope\editor\Edit;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\item\Item;
use pocketmine\item\ItemBlock;
use pocketmine\level\Level;
use pocketmine\math\VoxelRayTrace;
use pocketmine\Player;

class InfRay extends Creation{
    
    private ?Player $playerFrom = null;

    private ?Block $block = null;

    public function make() : Edit{
        
        $playerLevel = $this->playerFrom->getLevel();
        
        $fromPos = $this->playerFrom->asVector3();
        $toPos = $this->playerFrom->getTargetBlock(Level::Y_MAX);

        $positions = VoxelRayTrace::betweenPoints($fromPos, $toPos);
        
        $edit = new Edit($playerLevel, true);
        
        foreach($positions as $position){
            $edit->addBlock($position, $this->block->getId(), $this->block->getDamage());
        }
        
        return $edit;
        
    }

    /**
     * @return string
     */
    function getName(): string
    {
        return "InfRay";
    }

    /**
     * @param Player $player
     * @param array $args
     * @return bool
     */
    function parseArgs(Player $player, array $args): bool
    {
        $this->playerFrom = $player;

        if(empty($args[0])){
            return false;
        }

        try{

            if($block = Item::fromString($args[0])){
                $this->block = $block->getBlock();
            }

        }catch (\InvalidArgumentException $exception){

            return false;

        }



        return true;

    }

    /**
     * @return string
     */
    function getUsage(): string
    {
        return "<BlockName>";
    }
}