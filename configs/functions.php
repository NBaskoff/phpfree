<?php

/**
 * Глобальные функции-хелперы проекта phpfree
 */

if (!function_exists('env')) {
    /**
     * Получает значение из переменных окружения
     */
    function env(string $key, mixed $default = null): mixed
    {
        return \Core\Env::get($key, $default); // Получение из окружения
    }
}

if (!function_exists('config')) {
    /**
     * Глобальный хелпер для получения настроек
     */
    function config(string $key, mixed $default = null): mixed
    {
        return \Core\Config::get($key, $default); // Прокси к классу
    }
}

