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
     * @param string $currentDir
     * @return void
     */
    public static function initFromPublic(string $currentDir): void
    {
        $realPublic = realpath($currentDir);

        self::$public = $realPublic;
        self::$root = dirname($realPublic);

        self::$configs   = self::$root . DIRECTORY_SEPARATOR . 'config';
        self::$templates = self::$root . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'templates';
        self::$routes    = self::$root . DIRECTORY_SEPARATOR . 'routes';
    }

    /**
     * @param string $subPath
     * @return string
     */
    public static function root(string $subPath = ''): string
    {
        return self::join(self::$root, $subPath);
    }

    /**
     * @param string $subPath
     * @return string
     */
    public static function configs(string $subPath = ''): string
    {
        return self::join(self::$configs, $subPath);
    }

    /**
     * @param string $subPath
     * @return string
     */
    public static function templates(string $subPath = ''): string
    {
        return self::join(self::$templates, $subPath);
    }

    /**
     * @param string $subPath
     * @return string
     */
    public static function routes(string $subPath = ''): string
    {
        return self::join(self::$routes, $subPath);
    }

    /**
     * @param string $subPath
     * @return string
     */
    public static function public(string $subPath = ''): string
    {
        return self::join(self::$public, $subPath);
    }

    /**
     * @param string $base
     * @param string $subPath
     * @return string
     */
    private static function join(string $base, string $subPath): string
    {
        if (!$subPath) {
            return $base;
        }

        return $base . DIRECTORY_SEPARATOR . ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $subPath), DIRECTORY_SEPARATOR);
    }
}
