<?php

declare(strict_types=1);

namespace NexusMC\Kaleidoscope\editor;

class Editor{

    public static function edit(Edit $edit) : bool{
        $result = false;

        $level = $edit->getLevel();

        while($edit->hasNext()){
            $edit->readNext($x, $y, $z, $id, $meta);

            $level->setBlockIdAt($x,$y,$z,$id);
            $level->setBlockDataAt($x,$y,$z,$meta);
        }


        return $result;

    }



}