<?php

namespace Core;

class Autoloader
{
    /**
     * Регистрирует анонимную функцию автозагрузки в стеке PHP
     */
    public static function register(): void
    {
        spl_autoload_register(function ($class) {
            // 1. Преобразуем Namespace в путь к файлу: Core\View -> Core/View.php
            // Используем DIRECTORY_SEPARATOR для совместимости с Windows/Linux
            $path = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $class) . '.php';

            // 2. Получаем абсолютный путь от корня проекта через наш класс Path
            $file = Path::root($path);

            // 3. Если файл существует — подключаем его
            if (file_exists($file)) {
                require_once $file;
            }
        });
    }
}
