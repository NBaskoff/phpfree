<?php

namespace Core;

/**
 * Класс управления путями приложения
 */
class Path
{
    private static string $root = '';
    private static string $public = '';
    private static string $configs = '';
    private static string $templates = '';
    private static string $routes = '';

    /**
     * Инициализирует базовые пути системы из точки входа
     *
     * @param string $currentDir Путь к папке public (__DIR__)
     * @return void
     */
    public static function initFromPublic(string $currentDir): void
    {
        $realPublic = realpath($currentDir);
        self::$public = $realPublic;
        self::$root = dirname($realPublic);

        // Установка стандартных путей относительно корня
        self::$configs   = self::$root . DIRECTORY_SEPARATOR . 'config';
        self::$templates = self::$root . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'templates';
        self::$routes    = self::$root . DIRECTORY_SEPARATOR . 'routes';
    }

    /**
     * Путь к корню проекта или вложенному файлу
     *
     * @param string $subPath
     * @return string
     */
    public static function root(string $subPath = ''): string
    {
        $base = self::$root ?: dirname(__DIR__, 2);
        return self::join($base, $subPath);
    }

    /**
     * Путь к папке конфигураций или конкретному файлу
     *
     * @param string $subPath
     * @return string
     */
    public static function configs(string $subPath = ''): string
    {
        $base = self::$configs ?: self::root('config');
        return self::join($base, $subPath);
    }

    /**
     * Путь к папке шаблонов или конкретному файлу
     *
     * @param string $subPath
     * @return string
     */
    public static function templates(string $subPath = ''): string
    {
        $base = self::$templates ?: self::root('assets/templates');
        return self::join($base, $subPath);
    }

    /**
     * Путь к папке маршрутов или конкретному файлу
     *
     * @param string $subPath
     * @return string
     */
    public static function routes(string $subPath = ''): string
    {
        $base = self::$routes ?: self::root('routes');
        return self::join($base, $subPath);
    }

    /**
     * Путь к папке public или ассету
     *
     * @param string $subPath
     * @return string
     */
    public static function public(string $subPath = ''): string
    {
        $base = self::$public ?: self::root('public');
        return self::join($base, $subPath);
    }

    /**
     * Вспомогательный метод для безопасной склейки путей с учетом ОС
     *
     * @param string $base
     * @param string $subPath
     * @return string
     */
    private static function join(string $base, string $subPath): string
    {
        if (!$subPath) {
            return $base;
        }
        return $base . DIRECTORY_SEPARATOR . ltrim($subPath, '/\\');
    }
}
