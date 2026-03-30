<?php

namespace Core;

use Exception;
use InvalidArgumentException;
use ReflectionClass;

class Contract
{
    protected static array $bindings = []; // Загруженные связи
    protected static array $instances = []; // Кэш синглтонов
    protected static array $configs = []; // Список подключенных файлов конфигурации

    public static function make(string $abstract): object
    {
        if (isset(self::$instances[$abstract])) return self::$instances[$abstract]; // Возврат синглтона

        if (!isset(self::$bindings[$abstract])) {
            $files = implode("\n - ", self::$configs); // Формируем список файлов для ошибки
            throw new Exception(
                "Реализация для контракта [{$abstract}] не зарегистрирована.\n" .
                "Проверьте следующие файлы конфигурации:\n - {$files}"
            );
        }

        $concrete = self::$bindings[$abstract]['concrete'];
        $object = new Resolver()->resolveDependency($concrete); // PHP 8.4: инстанцирование без скобок

        if (self::$bindings[$abstract]['singleton'] ?? false) self::$instances[$abstract] = $object; // Сохранение синглтона
        return $object; // Возврат объекта
    }

    public static function loadConfig(string $path): void
    {
        if (!file_exists($path)) throw new Exception("Файл конфигурации не найден: {$path}"); // Проверка пути
        self::$configs[] = realpath($path); // Запоминаем путь к файлу конфигурации
        $config = require $path; // Загрузка массива
        foreach ($config['bindings'] ?? [] as $iface => $impl) self::bind($iface, $impl); // Регистрация bind
        foreach ($config['singletons'] ?? [] as $iface => $impl) self::singleton($iface, $impl); // Регистрация singleton
    }

    public static function bind(string $iface, string $impl): void // Регистрация связи
    {
        self::validate($iface, $impl);
        self::$bindings[$iface] = ['concrete' => $impl, 'singleton' => false];
    }

    public static function singleton(string $iface, string $impl): void // Регистрация синглтона
    {
        self::validate($iface, $impl);
        self::$bindings[$iface] = ['concrete' => $impl, 'singleton' => true];
    }

    protected static function validate(string $iface, string $impl): void
    {
        if (!interface_exists($iface) && !class_exists($iface)) throw new InvalidArgumentException("Контракт [{$iface}] не существует."); // Валидация интерфейса
        if (!class_exists($impl)) throw new InvalidArgumentException("Класс реализации [{$impl}] не найден."); // Валидация класса
        if (new ReflectionClass($impl)->isAbstract()) throw new InvalidArgumentException("Класс [{$impl}] абстрактный и не может быть инстанцирован."); // Проверка на абстрактность
        if (interface_exists($iface) && !is_subclass_of($impl, $iface) && $impl !== $iface) throw new InvalidArgumentException("Класс [{$impl}] не соответствует контракту [{$iface}]."); // Проверка наследования
    }
}
