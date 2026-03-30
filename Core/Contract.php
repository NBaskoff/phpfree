<?php

namespace Core;

use Exception;
use InvalidArgumentException;
use ReflectionClass;

class Contract
{
    protected static array $bindings = []; // Связи из конфига
    protected static array $instances = []; // Синглтоны

    public static function make(string $abstract): object
    {
        if (isset(self::$instances[$abstract])) return self::$instances[$abstract]; // Возврат синглтона
        $concrete = self::$bindings[$abstract]['concrete'] ?? throw new Exception("Контракт [{$abstract}] не описан в config/contracts.php"); // Проверка конфига
        $object = new Resolver()->resolveDependency($concrete); // PHP 8.4: создание и вызов без скобок
        if (self::$bindings[$abstract]['singleton'] ?? false) self::$instances[$abstract] = $object; // Сохранение синглтона
        return $object; // Возврат объекта
    }

    public static function loadConfig(string $path): void
    {
        if (!file_exists($path)) throw new Exception("Файл конфига не найден: {$path}"); // Проверка файла
        $config = require $path; // Загрузка массива
        foreach ($config['bindings'] ?? [] as $iface => $impl) self::bind($iface, $impl); // Регистрация
        foreach ($config['singletons'] ?? [] as $iface => $impl) self::singleton($iface, $impl); // Регистрация
    }

    public static function bind(string $iface, string $impl): void { self::validate($iface, $impl); self::$bindings[$iface] = ['concrete' => $impl, 'singleton' => false]; } // bind

    public static function singleton(string $iface, string $impl): void { self::validate($iface, $impl); self::$bindings[$iface] = ['concrete' => $impl, 'singleton' => true]; } // singleton

    protected static function validate(string $iface, string $impl): void
    {
        if (!interface_exists($iface) && !class_exists($iface)) throw new InvalidArgumentException("Контракт [{$iface}] не найден"); // Проверка контракта
        if (!class_exists($impl)) throw new InvalidArgumentException("Реализация [{$impl}] не найдена"); // Проверка реализации
        if (new ReflectionClass($impl)->isAbstract()) throw new InvalidArgumentException("Класс [{$impl}] абстрактный и не может быть реализацией"); // Проверка на абстрактность (PHP 8.4)
        if (interface_exists($iface) && !is_subclass_of($impl, $iface) && $impl !== $iface) throw new InvalidArgumentException("Класс [{$impl}] не реализует [{$iface}]"); // Проверка соответствия
    }
}
