<?php

namespace Core;

class App
{
    public static function init(string $publicPath): void
    {
        require_once __DIR__ . '/Path.php'; // Инициализация путей
        Path::initFromPublic($publicPath); // Настройка путей

        require_once Path::root('Core/Autoloader.php'); // Автозагрузчик
        Autoloader::register(); // Регистрация

        require_once Path::root('Core/Env.php'); // Переменные окружения
        Env::load(Path::root('.env')); // Загрузка .env

        $request = new Request(); // Создание запроса
        $resolver = new Resolver($request); // Создание резолвера

        Contract::setResolver($resolver); // Привязка к контракту
        Contract::loadConfig(Path::configs('contracts.php')); // Загрузка биндингов
    }
}
