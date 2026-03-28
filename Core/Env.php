<?php

namespace Core {

    class Env
    {
        /**
         * Загружает переменные из .env файла
         */
        public static function load(string $path): void
        {
            if (!file_exists($path)) {
                return;
            }

            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            foreach ($lines as $line) {
                $line = trim($line);
                if (!$line || str_starts_with($line, '#')) continue;

                if (!str_contains($line, '=')) continue;

                [$name, $value] = explode('=', $line, 2);

                $name  = trim($name);
                $value = trim($value);

                $_ENV[$name] = $value;
                putenv("{$name}={$value}");
            }
        }

        /**
         * Внутренняя логика получения значения
         */
        public static function get(string $key, mixed $default = null): mixed
        {
            $value = $_ENV[$key] ?? getenv($key);

            if ($value === false || $value === null) {
                return $default;
            }

            return match (strtolower((string)$value)) {
                'true', '(true)'   => true,
                'false', '(false)' => false,
                'empty', '(empty)' => '',
                'null', '(null)'   => null,
                default            => $value,
            };
        }
    }
}

/**
 * Глобальное пространство имен для функции-хелпера
 */
namespace {
    if (!function_exists('env')) {
        function env(string $key, mixed $default = null): mixed
        {
            return \Core\Env::get($key, $default);
        }
    }
}
