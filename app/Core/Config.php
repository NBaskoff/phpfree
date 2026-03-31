<?php

namespace Core;

class Config
{
    protected static array $items = []; // Кеш загруженных конфигов

    /**
     * Получает значение из конфигурации (например, 'db.mysql.host')
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $parts = explode('.', $key); // Разбиваем ключ на части
        $file = array_shift($parts); // Первая часть — имя файла

        if (!isset(self::$items[$file])) {
            $path = Path::configs($file . '.php'); // Формируем путь
            self::$items[$file] = file_exists($path) ? require $path : []; // Загружаем файл
        }

        $config = self::$items[$file]; // Текущий массив данных

        foreach ($parts as $part) {
            if (!is_array($config) || !isset($config[$part])) return $default; // Если ключа нет — дефолт
            $config = $config[$part]; // Погружаемся глубже
        }

        return $config; // Возвращаем значение
    }
}
