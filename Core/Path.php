<?php

namespace Core;

class Path
{
    private static string $root = '';
    private static string $public = '';

    /**
     * Инициализация базовых путей (вызывается в public/index.php)
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
        return self::join($base, $subPath);
    }

    /**
     * Путь к папке public или вложенному ассету
     */
    public static function public(string $subPath = ''): string
    {
        $base = self::$public ?: (self::root() . DIRECTORY_SEPARATOR . 'public');
        return self::join($base, $subPath);
    }

    /**
     * Вспомогательный метод для безопасной склейки путей
     */
    private static function join(string $base, string $subPath): string
    {
        if (!$subPath) {
            return $base;
        }

        // Очищаем вложенный путь от любых слешей в начале и склеиваем через системный разделитель
        return $base . DIRECTORY_SEPARATOR . ltrim($subPath, '/\\');
    }
}
