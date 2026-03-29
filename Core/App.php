<?php

namespace Core;

/**
 * Класс инициализации приложения (Web и CLI)
 */
class App
{
    /**
     * Подготавливает окружение: пути, автозагрузку, конфиги и .env
     *
     * @param string $publicPath Путь к папке public
     */
    public static function init(string $publicPath): void
    {
        // 1. Инициализация путей (Path::configs, Path::routes и т.д.)
        require_once __DIR__ . '/Path.php';
        Path::initFromPublic($publicPath);

        // 2. Регистрация автозагрузчика классов
        require_once Path::root('Core/Autoloader.php');
        Autoloader::register();

        // 3. Загрузка функций-хелперов и переменных окружения
        require_once Path::root('Core/Env.php');
        Env::load(Path::root('.env'));

        // 4. Загрузка конфигурации DI-контейнера через новый метод configs()
        Contract::loadConfig(Path::configs('contracts.php'));
    }
}
