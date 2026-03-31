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

