<?php

namespace Core;

use InvalidArgumentException;
use Exception;

class Contract
{
    protected static array $bindings = [];
    protected static array $instances = [];

    /**
     * Загрузка конфигурации из PHP файла (обычно config/contracts.php)
     * @throws Exception
     */
    public static function loadConfig(string $path): void
    {
        if (!file_exists($path)) {
            throw new Exception("Файл конфигурации контрактов не найден: {$path}");
        }

        $config = require $path;

        if (!is_array($config)) {
            throw new Exception("Конфиг контрактов должен возвращать массив.");
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
     * Регистрация обычной связи (каждый раз новый объект)
     */
    public static function bind(string $interface, string $implementation): void
    {
        self::validate($interface, $implementation);
        self::$bindings[$interface] = [
            'concrete' => $implementation,
            'singleton' => false
        ];
    }

    /**
     * Регистрация синглтона (один объект на всё время работы)
     */
    public static function singleton(string $interface, string $implementation): void
    {
        self::validate($interface, $implementation);
        self::$bindings[$interface] = [
            'concrete' => $implementation,
            'singleton' => true
        ];
    }

    /**
     * Создание объекта (основной метод движка)
     *
     * @template T
     * @param class-string<T> $abstract Полное имя интерфейса или класса
     * @return T
     * @throws Exception
     */
    public static function make(string $abstract)
    {
        // 1. Если для этого интерфейса/класса есть зарегистрированная связь
        if (isset(self::$bindings[$abstract])) {
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

        // 2. Если связи нет, но класс существует — создаем его напрямую
        if (class_exists($abstract)) {
            return new $abstract();
        }

        throw new Exception("Ошибка контейнера: Связь для '{$abstract}' не зарегистрирована и класс не найден.");
    }

    /**
     * Проверка существования классов и интерфейсов
     */
    protected static function validate(string $interface, string $implementation): void
    {
        if (!interface_exists($interface) && !class_exists($interface)) {
            throw new InvalidArgumentException("Контракт (интерфейс/класс) '{$interface}' не найден.");
        }

        if (!class_exists($implementation)) {
            throw new InvalidArgumentException("Реализация (класс) '{$implementation}' не найдена.");
        }

        // Проверяем, реализует ли класс данный интерфейс (или наследует класс)
        if (!is_subclass_of($implementation, $interface) && $implementation !== $interface) {
            throw new InvalidArgumentException("Класс '{$implementation}' не соответствует контракту '{$interface}'.");
        }
    }
}
