<?php

namespace Core;

use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use Exception;
use InvalidArgumentException;

class Contract
{
    protected static array $bindings = [];
    protected static array $instances = [];

    /**
     * Загрузка конфигурации из PHP файла
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
     * Регистрация обычной связи
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
     * Регистрация синглтона
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
     * Основной метод создания объекта (с поддержкой Auto-wiring)
     *
     * @template T
     * @param class-string<T> $abstract
     * @return object
     * @throws Exception
     */
    public static function make(string $abstract): object
    {
        // 1. Если это синглтон и он уже создан - возвращаем готовый экземпляр
        if (isset(self::$instances[$abstract])) {
            return self::$instances[$abstract];
        }

        // 2. Определяем конкретный класс (из биндингов или сам абстракт)
        $concrete = self::$bindings[$abstract]['concrete'] ?? $abstract;

        // 3. Рекурсивно разрешаем зависимости через Reflection
        $object = self::resolve($concrete);

        // 4. Если зарегистрирован как синглтон - сохраняем экземпляр
        if (isset(self::$bindings[$abstract]['singleton']) && self::$bindings[$abstract]['singleton']) {
            self::$instances[$abstract] = $object;
        }

        return $object;
    }

    /**
     * Рекурсивное создание объекта через анализ конструктора
     * @throws ReflectionException
     * @throws Exception
     */
    private static function resolve(string $concrete): object
    {
        $reflection = new ReflectionClass($concrete);

        if (!$reflection->isInstantiable()) {
            throw new Exception("Класс [{$concrete}] не может быть создан (интерфейс или абстрактный класс).");
        }

        $constructor = $reflection->getConstructor();

        // Если конструктора нет - просто создаем объект
        if (is_null($constructor)) {
            return new $concrete();
        }

        // Получаем параметры конструктора
        $parameters = $constructor->getParameters();
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $type = $parameter->getType();

            // Если тип не указан или это примитив (string, int)
            if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                    continue;
                }
                throw new Exception("Не удалось разрешить зависимость [{$parameter->getName()}] в классе [{$concrete}].");
            }

            // РЕКУРСИЯ: запрашиваем зависимость у Contract::make
            $dependencies[] = self::make($type->getName());
        }

        return $reflection->newInstanceArgs($dependencies);
    }

    /**
     * Валидация связи
     */
    protected static function validate(string $interface, string $implementation): void
    {
        if (!interface_exists($interface) && !class_exists($interface)) {
            throw new InvalidArgumentException("Контракт [{$interface}] не найден.");
        }
        if (!class_exists($implementation)) {
            throw new InvalidArgumentException("Класс реализации [{$implementation}] не найден.");
        }
        if (!is_subclass_of($implementation, $interface) && $implementation !== $interface) {
            throw new InvalidArgumentException("Класс [{$implementation}] не соответствует контракту [{$interface}].");
        }
    }
}
