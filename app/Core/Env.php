<?php

namespace Core;

class Env
{
    public static function load(string $path): void
    {
        if (!file_exists($path)) return; // Проверка файла

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES); // Чтение строк

        foreach ($lines as $line) {
            $line = trim($line); // Очистка
            if (!$line || str_starts_with($line, '#')) continue; // Пропуск комментариев
            if (!str_contains($line, '=')) continue; // Пропуск некорректных строк

            [$name, $value] = explode('=', $line, 2); // Разделение

            $_ENV[trim($name)] = trim($value); // В $_ENV
            putenv(trim($name) . "=" . trim($value)); // В окружение системы
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? getenv($key); // Поиск значения

        if ($value === false || $value === null) return $default; // Возврат дефолта

        return match (strtolower((string)$value)) {
            'true', '(true)'   => true, // Приведение к bool
            'false', '(false)' => false,
            'empty', '(empty)' => '', // Пустая строка
            'null', '(null)'   => null, // Null тип
            default            => $value, // Исходная строка
        };
    }
}
