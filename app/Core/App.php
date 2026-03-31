<?php

namespace Core;

class App
{
    public static function init(): void
    {
        require_once __DIR__ . '/Autoloader.php'; // Подключаем загрузчик
        require_once __DIR__ . '/Path.php'; // Подключаем пути
        Path::initFromRoot(dirname(__DIR__, 2));

        require_once Path::configs('functions.php');
        Autoloader::register(); // Регистрируем автозагрузку классов из /app
        Env::load(Path::root('.env'));
        Contract::loadConfig(Path::configs('contracts.php'));
    }


}
