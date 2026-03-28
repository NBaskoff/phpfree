<?php

namespace Core;

use InvalidArgumentException;
use Exception;

class Contract {
    protected static array $bindings = [];
    protected static array $instances = [];

    /**
     * Загрузка конфигурации из PHP файла
     */
    public static function loadConfig(string $path): void {
        if (!file_exists($path)) {
            die("Ошибка Contract: Файл конфигурации не найден по пути: {$path}");
        }

        $config = require $path;
        if (!is_array($config)) {
            die("Ошибка Contract: Файл конфигурации должен возвращать массив.");
        }

        if (isset($config['bindings'])) {
            foreach ($config['bindings'] as $interface => $implementation) {
                self::bind($interface, $implementation);
            }
        }

        if (isset($config['singletons'])) {
            foreach ($config['singletons'] as $interface => $implementation) {
                self::singleton($interface, $implementation);
            }
        }
    }

    /**
     * Регистрация обычной связи
     */
    public static function bind(string $interface, string $implementation): void {
        self::validate($interface, $implementation);
        self::$bindings[$interface] = [
            'concrete' => $implementation,
            'singleton' => false
        ];
    }

    /**
     * Регистрация синглтона
     */
    public static function singleton(string $interface, string $implementation): void {
        self::validate($interface, $implementation);
        self::$bindings[$interface] = [
            'concrete' => $implementation,
            'singleton' => true
        ];
    }

    /**
     * @template T
     * @param class-string<T> $abstract
     * @return T
     */
    public static function make(string $abstract) {
        if (!isset(self::$bindings[$abstract])) {
            if (class_exists($abstract)) {
                return new $abstract();
            }
            throw new Exception("Связь для {$abstract} не найдена.");
        }

        $binding = self::$bindings[$abstract];
        $concrete = $binding['concrete'];

        if ($binding['singleton']) {
            if (!isset(self::$instances[$abstract])) {
                self::$instances[$abstract] = new $concrete();
            }
            return self::$instances[$abstract];
        }

        return new $concrete();
    }

    protected static function validate(string $interface, string $implementation): void {
        if (!interface_exists($interface) && !class_exists($interface)) {
            throw new InvalidArgumentException("Интерфейс или класс {$interface} не найден.");
        }
        if (!class_exists($implementation)) {
            throw new InvalidArgumentException("Класс реализации {$implementation} не найден.");
        }
        if (!is_subclass_of($implementation, $interface) && $implementation !== $interface) {
            throw new InvalidArgumentException("Класс {$implementation} не реализует {$interface}.");
        }
    }
}