<?php

namespace Core;

class App
{
    public static function init(string $publicPath): void
    {
        require_once __DIR__ . '/Path.php'; // Пути
        Path::initFromPublic($publicPath); // Инициализация Path
        require_once Path::root('Core/Autoloader.php'); // Автозагрузчик
        Autoloader::register(); // Регистрация
        require_once Path::root('Core/Env.php'); // Окружение
        Env::load(Path::root('.env')); // Загрузка .env
        $request = new Request(); // Системный запрос
        $resolver = new Resolver($request); // Системный резолвер
        Contract::setResolver($resolver); // Привязка к Contract
        Contract::loadConfig(Path::configs('contracts.php')); // Конфиг
    }
}
