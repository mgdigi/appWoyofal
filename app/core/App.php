<?php

// 


namespace App\Core;

class App
{
    private static ?DIContainer $container = null;

    public static function init(): void
    {
        self::$container = DIContainer::getInstance();
    }

    public static function get(string $identifier): object
    {
        if (self::$container === null) {
            self::init();
        }

        return self::$container->get($identifier);
    }

    
}

