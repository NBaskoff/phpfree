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
            $path = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php'; // Имя файла
            $file = Path::app($path); // Ищем в папке, указанной в paths.php под ключом 'app'

            if (file_exists($file)) require_once $file; // Подключаем
        });
    }
}
