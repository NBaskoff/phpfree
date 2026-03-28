<?php

namespace Core;

class Path
{
    private static string $root = '';
    private static string $public = '';

    /**
     * Инициализация путей из index.php (передайте __DIR__)
     */
    public static function initFromPublic(string $currentDir): void
    {
        $realPublic = realpath($currentDir);
        self::$public = $realPublic;
        self::$root = dirname($realPublic);
    }

    /**
     * Путь к корню или вложенному файлу.
     * Принимает аргументы вида 'config/app.php' или '/config/app.php'
     */
    public static function root(string $subPath = ''): string
    {
        $base = self::$root ?: dirname(__DIR__, 2);

        if (!$subPath) {
            return $base;
        }

        // Удаляем любые слеши в начале входящей строки и приклеиваем через системный разделитель
        return $base . DIRECTORY_SEPARATOR . ltrim($subPath, '/\\');
    }

    /**
     * Путь к папке public или вложенному ассету.
     * Принимает аргументы вида 'js/app.js' или '/js/app.js'
     */
    public static function public(string $subPath = ''): string
    {
        $base = self::$public ?: (self::root() . DIRECTORY_SEPARATOR . 'public');

        if (!$subPath) {
            return $base;
        }

        return $base . DIRECTORY_SEPARATOR . ltrim($subPath, '/\\');
    }
}
