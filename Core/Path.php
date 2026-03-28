<?php

namespace Core;

class Path
{
    private static string $root = '';
    private static string $public = '';
    private static string $config = '';
    private static string $templates = '';

    public static function initFromPublic(string $currentDir): void
    {
        $realPublic = realpath($currentDir);
        self::$public = $realPublic;
        self::$root = dirname($realPublic);

        // По умолчанию конфиги в /config, шаблоны в /assets/templates
        self::$config = self::$root . DIRECTORY_SEPARATOR . 'config';
        self::$templates = self::$root . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'templates';
    }

    public static function root(string $subPath = ''): string
    {
        $base = self::$root ?: dirname(__DIR__, 2);
        return self::join($base, $subPath);
    }

    public static function config(string $subPath = ''): string
    {
        return self::join(self::$config, $subPath);
    }

    public static function templates(string $subPath = ''): string
    {
        return self::join(self::$templates, $subPath);
    }

    public static function public(string $subPath = ''): string
    {
        return self::join(self::$public, $subPath);
    }

    public static function setTemplatesDir(string $path): void
    {
        self::$templates = self::root($path);
    }

    private static function join(string $base, string $subPath): string
    {
        if (!$subPath) return $base;
        return $base . DIRECTORY_SEPARATOR . ltrim($subPath, '/\\');
    }
}
