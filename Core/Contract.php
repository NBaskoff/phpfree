<?php

namespace Core;

use Exception;
use InvalidArgumentException;

class Contract
{
    protected static array $bindings = []; // Хранилище связей
    protected static array $instances = []; // Хранилище синглтонов

    public static function make(string $abstract): object
    {
        if (isset(self::$instances[$abstract])) return self::$instances[$abstract]; // Возврат синглтона
        $concrete = self::$bindings[$abstract]['concrete'] ?? $abstract; // Поиск реализации
        $object = (new Resolver())->resolveDependency($concrete); // Создание Resolver без аргументов
        if (isset(self::$bindings[$abstract]['singleton']) && self::$bindings[$abstract]['singleton']) self::$instances[$abstract] = $object; // Сохранение синглтона
        return $object; // Возврат
    }

    public static function loadConfig(string $path): void
    {
        if (!file_exists($path)) throw new Exception("Конфиг контрактов не найден: {$path}"); // Проверка файла
        $config = require $path; // Загрузка массива
        foreach ($config['bindings'] ?? [] as $iface => $impl) self::bind($iface, $impl); // Регистрация обычных связей
        foreach ($config['singletons'] ?? [] as $iface => $impl) self::singleton($iface, $impl); // Регистрация синглтонов
    }

    public static function bind(string $iface, string $impl): void
    {
        self::validate($iface, $impl); // Валидация перед записью
        self::$bindings[$iface] = ['concrete' => $impl, 'singleton' => false]; // Сохранение связи
    }

    public static function singleton(string $iface, string $impl): void
    {
        self::validate($iface, $impl); // Валидация перед записью
        self::$bindings[$iface] = ['concrete' => $impl, 'singleton' => true]; // Сохранение синглтона
    }

    protected static function validate(string $iface, string $impl): void
    {
        if (!interface_exists($iface) && !class_exists($iface)) throw new InvalidArgumentException("Контракт [{$iface}] не найден"); // Проверка интерфейса/класса
        if (!class_exists($impl)) throw new InvalidArgumentException("Реализация [{$impl}] не найдена"); // Проверка реализации
    }
}
