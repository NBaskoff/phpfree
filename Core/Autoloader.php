<?php

namespace Core;

/**
 * Класс автоматической загрузки классов проекта
 */
class Autoloader
{
    /**
     * @return void
     */
    public static function register(): void
    {
        spl_autoload_register(static function (string $class): void {
            $path = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
            $file = Path::root($path);

            if (file_exists($file)) {
                require $file;
            }
        });
    }
}
