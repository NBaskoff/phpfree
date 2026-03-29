<?php

namespace Core;

/**
 * Класс инициализации приложения
 */
class App
{
    /**
     * Подготавливает окружение для Web и CLI
     *
     * @param string $publicPath Путь к папке public
     */
    public static function init(string $publicPath): void
    {
        // 1. Инициализация путей
        require_once __DIR__ . '/Path.php';
        Path::initFromPublic($publicPath);

        // 2. Регистрация автозагрузчика
        require_once Path::root('Core/Autoloader.php');
        Autoloader::register();

        // 3. Загрузка переменных окружения и функций-хелперов
        require_once Path::root('Core/Env.php');
        Env::load(Path::root('.env'));

        // 4. Загрузка конфигурации DI-контейнера
        Contract::loadConfig(Path::configs('contracts.php'));
    }
}
