<?php

namespace Core;

/**
 * Класс управления конфигурацией приложения
 */
class Config
{
    protected static array $items = []; // Кэш всех настроек

    /**
     * Загружает все PHP файлы из директории /config
     */
    public static function load(): void
    {
        $dir = Path::configs(); // Путь к папке config
        if (!is_dir($dir)) return; // Проверка директории

        foreach (glob($dir . '/*.php') as $file) {
            $key = basename($file, '.php'); // Имя файла без расширения
            self::$items[$key] = require $file; // Загрузка массива данных
        }
    }

    /**
     * Статический метод получения значения (поддерживает "точку")
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $data = self::$items; // Текущий срез конфига
        foreach (explode('.', $key) as $segment) {
            if (!is_array($data) || !isset($data[$segment])) return $default; // Если путь не найден — дефолт
            $data = $data[$segment]; // Проваливаемся глубже
        }

        return $data; // Возврат найденного значения
    }
}

/**
 * Глобальный хелпер-сахар для быстрого доступа к конфигу
 */
namespace {
    if (!function_exists('config')) {
        function config(string $key, mixed $default = null): mixed
        {
            return \Core\Config::get($key, $default); // Вызов статического метода
        }
    }
}
