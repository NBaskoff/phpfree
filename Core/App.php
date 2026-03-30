<?php

namespace Core;

class App
{
    public static function init(?string $publicPath = null): void
    {
        require_once __DIR__ . '/Path.php'; // Пути
        $resolvedPath = $publicPath ?? dirname(__DIR__); // Динамическое определение
        Path::initFromPublic($resolvedPath); // Инициализация Path
        require_once __DIR__ . '/Autoloader.php'; // Автозагрузчик
        Autoloader::register(); // Регистрация
        require_once __DIR__ . '/Env.php'; // Окружение
        if (file_exists(Path::root('.env'))) Env::load(Path::root('.env')); // Загрузка .env
        Contract::loadConfig(Path::configs('contracts.php')); // Контракты
    }
}
