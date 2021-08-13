<?php

declare(strict_types=1);

namespace NexusMC\Kaleidoscope\creator;

class CreationFactory{

    private static array $creations = [];

    public static function registerCreation(Creation $creation)
    {

        $name = strtolower($creation->getName());

        self::$creations[$name] = $creation->getClass();

    }

    public static function getCreation(string $name) : ?Creation
    {
        if(!empty(self::$creations[strtolower($name)])){

            $creation = self::$creations[strtolower($name)];

            if(!$creation instanceof Creation){
                return null;
            }
            return $creation;
        }

        return null;
    }

    public static function registerDefaults() : void
    {
        $classes = [
            new InfRay(),
            new Populate()
        ];

        foreach ($classes as $class){

            self::registerCreation($class);

        }

    }

}