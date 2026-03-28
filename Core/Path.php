<?php

namespace Core;

class Path
{
    private static string $root = '';
    private static string $public = '';

    /**
     * Инициализация путей из index.php (передайте __DIR__)
     * Автоматически определяет корень (уровень выше) и папку public
     */
    public static function initFromPublic(string $currentDir): void
    {
        $realPublic = realpath($currentDir);
        self::$public = $realPublic;
        self::$root = dirname($realPublic);
    }

    /**
     * Путь к корню или вложенному файлу
     */
    public static function root(string $subPath = ''): string
    {
        $base = self::$root ?: dirname(__DIR__, 2);
        return $base . ($subPath ? DIRECTORY_SEPARATOR . ltrim($subPath, DIRECTORY_SEPARATOR) : '');
    }

    /**
     * Путь к папке public или вложенному ассету
     */
    public static function public(string $subPath = ''): string
    {
        $base = self::$public ?: (self::root() . DIRECTORY_SEPARATOR . 'public');
        return $base . ($subPath ? DIRECTORY_SEPARATOR . ltrim($subPath, DIRECTORY_SEPARATOR) : '');
    }
}
