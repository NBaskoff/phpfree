<?php

namespace Core;

class Path
{
    private static string $root = '';
    private static string $public = '';
    private static string $config = '';
    private static string $templates = '';

    /**
     * Инициализация базовых путей из точки входа (public/index.php)
     */
    public static function initFromPublic(string $currentDir): void
    {
        $realPublic = realpath($currentDir);
        self::$public = $realPublic;
        self::$root = dirname($realPublic);

        // Настройки папок по умолчанию
        self::$config = self::$root . DIRECTORY_SEPARATOR . 'config';
        // Указываем путь к шаблонам в assets/templates
        self::$templates = self::$root . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'templates';
    }

    /**
     * Путь к корню проекта или вложенному файлу
     */
    public static function root(string $subPath = ''): string
    {
        $base = self::$root ?: dirname(__DIR__, 2);
        return self::join($base, $subPath);
    }

    /**
     * Путь к папке конфигурации или конкретному конфигу
     */
    public static function config(string $subPath = ''): string
    {
        $base = self::$config ?: self::root('config');
        return self::join($base, $subPath);
    }

    /**
     * Путь к папке шаблонов или конкретному файлу шаблона
     */
    public static function templates(string $subPath = ''): string
    {
        $base = self::$templates ?: self::root('assets/templates');
        return self::join($base, $subPath);
    }

    /**
     * Путь к папке public или ассету внутри неё
     */
    public static function public(string $subPath = ''): string
    {
        $base = self::$public ?: self::root('public');
        return self::join($base, $subPath);
    }

    /**
     * Позволяет вручную переопределить папку шаблонов из index.php
     */
    public static function setTemplatesDir(string $path): void
    {
        self::$templates = self::root($path);
    }

    /**
     * Вспомогательный метод для безопасной склейки путей
     */
    private static function join(string $base, string $subPath): string
    {
        if (!$subPath) {
            return $base;
        }

        // Убираем лишние слеши и склеиваем через системный разделитель
        return $base . DIRECTORY_SEPARATOR . ltrim($subPath, '/\\');
    }
}
