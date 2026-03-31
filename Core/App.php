<?php

namespace Core;

class App
{
    public static function init(): void
    {
        require_once __DIR__ . '/Autoloader.php'; // Автозагрузчик
        require_once __DIR__ . '/Path.php'; // Пути
        Path::initFromRoot(dirname(__DIR__)); // Инициализация Path
        require_once Path::configs('functions.php'); // ПОДКЛЮЧАЕМ ГЛОБАЛЬНЫЙ САХАР (env, view)
        Autoloader::register(); // Регистрация
        Env::load(Path::root('.env')); // Загрузка .env
        Contract::loadConfig(Path::configs('contracts.php')); // Контракты
    }
}
