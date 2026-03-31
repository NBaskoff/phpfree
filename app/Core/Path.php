<?php

namespace Core;

/**
 * Класс управления путями приложения phpfree
 */
class Path
{
    private static string $root = ''; // Корень проекта
    private static array $paths = []; // Массив путей из configs/paths.php

    /**
     * Инициализация путей от корня проекта
     */
    public static function init(string $rootDir, ?string $configDir = null): void
    {
        self::$root = $rootDir; // Устанавливаем корень
        if (empty($configDir)) {
            $configDir = self::$root . DIRECTORY_SEPARATOR . "configs";
        }
        self::$paths['configs'] = $configDir;
        $configPath = self::configs("paths.php"); // Путь к файлу путей
        if (file_exists($configPath)) {
            $customPaths = require $configPath; // Загружаем пользовательские пути
            self::$paths = array_merge(self::$paths, $customPaths); // Объединяем массивы
        }
    }

    /** Путь от корня */
    public static function root(string $subPath = ''): string
    {
        return self::getPath(self::$root, $subPath);
    }

    /** Путь к конфигурациям (в корне /configs) */
    public static function configs(string $subPath = ''): string
    {
        return self::getPath(self::$paths['configs'], $subPath);
    }

    /** Путь к шаблонам */
    public static function templates(string $subPath = ''): string
    {
        return self::getPath('templates', $subPath);
    }

    /** Путь к файлам маршрутов */
    public static function routes(string $subPath = ''): string
    {
        return self::getPath('routes', $subPath);
    }

    /** Путь к публичной директории */
    public static function public(string $subPath = ''): string
    {
        return self::getPath('public', $subPath);
    }

    /** Путь к файлам миграций */
    public static function migrations(string $subPath = ''): string
    {
        return self::getPath('migrations', $subPath);
    }

    /** Путь к директории приложения (/app) */
    public static function app(string $subPath = ''): string
    {
        return self::getPath('app', $subPath);
    }

    /** Внутренний метод получения пути из массива */
    private static function getPath(string $key, string $subPath): string
    {
        $base = self::$paths[$key] ?? $key; // Значение из конфига или сам ключ
        // Если путь относительный — клеим к корню
        $fullBase = str_contains($base, self::$root) ? $base : self::$root . DIRECTORY_SEPARATOR . $base;
        return self::join($fullBase, $subPath);
    }

    /** Универсальная склейка путей с чисткой слешей */
    private static function join(string $base, string $subPath): string
    {
        if (!$subPath) return $base; // Если доп. пути нет — возвращаем базу
        return $base . DIRECTORY_SEPARATOR . ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $subPath), DIRECTORY_SEPARATOR);
    }
}
