<?php

namespace Core;

use Exception;
use InvalidArgumentException;

class Contract
{
    protected static array $bindings = []; // Хранилище связей
    protected static array $instances = []; // Хранилище синглтонов
    private static ?Resolver $resolver = null; // Объект резолвера

    public static function setResolver(Resolver $resolver): void { self::$resolver = $resolver; } // Установка резолвера

    public static function getResolver(): Resolver { return self::$resolver ?? throw new Exception("Resolver не установлен"); } // Получение резолвера

    public static function make(string $abstract): object
    {
        if (isset(self::$instances[$abstract])) return self::$instances[$abstract]; // Возврат синглтона
        $concrete = self::$bindings[$abstract]['concrete'] ?? $abstract; // Поиск реализации
        $object = self::getResolver()->resolveDependency($concrete); // Делегирование создания Резолверу
        if (isset(self::$bindings[$abstract]['singleton']) && self::$bindings[$abstract]['singleton']) self::$instances[$abstract] = $object; // Сохранение синглтона
        return $object; // Возврат объекта
    }

    public static function loadConfig(string $path): void
    {
        if (!file_exists($path)) throw new Exception("Конфиг не найден: {$path}"); // Проверка файла
        $config = require $path; // Загрузка массива
        foreach ($config['bindings'] ?? [] as $iface => $impl) self::bind($iface, $impl); // Биндинг
        foreach ($config['singletons'] ?? [] as $iface => $impl) self::singleton($iface, $impl); // Синглтоны
    }

    public static function bind(string $iface, string $impl): void { self::validate($iface, $impl); self::$bindings[$iface] = ['concrete' => $impl, 'singleton' => false]; } // Регистрация связи

    public static function singleton(string $iface, string $impl): void { self::validate($iface, $impl); self::$bindings[$iface] = ['concrete' => $impl, 'singleton' => true]; } // Регистрация синглтона

    protected static function validate(string $iface, string $impl): void
    {
        if (!interface_exists($iface) && !class_exists($iface)) throw new InvalidArgumentException("Контракт [{$iface}] не найден"); // Валидация контракта
        if (!class_exists($impl)) throw new InvalidArgumentException("Реализация [{$impl}] не найдена"); // Валидация класса
    }
}
