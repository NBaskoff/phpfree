<?php

namespace Core;

use ReflectionMethod;
use ReflectionClass;
use ReflectionNamedType;
use Exception;

class Resolver
{
    public function __construct() {} // Конструктор теперь пустой

    public function resolveMethodArgs(string $controller, string $method, array $urlParams): array
    {
        $reflection = new ReflectionMethod($controller, $method); // Рефлексия метода
        $args = []; // Массив аргументов
        foreach ($reflection->getParameters() as $param) {
            $type = $param->getType(); // Тип параметра
            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) $args[] = Contract::make($type->getName()); // Разрешение через Contract
            else $args[] = $urlParams[$param->getName()] ?? ($param->isDefaultValueAvailable() ? $param->getDefaultValue() : null); // Данные из URL или дефолт
        }
        return $args; // Возврат собранных аргументов
    }

    public function resolveDependency(string $className): mixed
    {
        if ($className === Request::class) return new Request(); // Создаем Request на лету при запросе
        if (is_subclass_of($className, \Requests\BaseRequest::class)) return new $className(); // Создание валидаторов
        return $this->autoWire($className); // Автоматическая сборка
    }

    private function autoWire(string $concrete): object
    {
        $reflection = new ReflectionClass($concrete); // Рефлексия класса
        if (!$reflection->isInstantiable()) throw new Exception("Класс [{$concrete}] не инстанцируем"); // Проверка
        $constructor = $reflection->getConstructor(); // Получение конструктора
        if (!$constructor) return new $concrete(); // Создание без аргументов
        $dependencies = array_map(fn($p) => Contract::make($p->getType()->getName()), $constructor->getParameters()); // Рекурсия через Contract
        return $reflection->newInstanceArgs($dependencies); // Создание с зависимостями
    }
}
