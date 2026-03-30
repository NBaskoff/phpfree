<?php

namespace Core;

use ReflectionMethod;
use ReflectionClass;
use ReflectionNamedType;
use Exception;

class Resolver
{
    public function resolveMethodArgs(string $controller, string $method, array $urlParams): array
    {
        $reflection = new ReflectionMethod($controller, $method); // Рефлексия метода
        $args = []; // Массив аргументов
        foreach ($reflection->getParameters() as $param) {
            $type = $param->getType(); // Тип параметра
            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $name = $type->getName(); // Имя типа
                $args[] = interface_exists($name) ? Contract::make($name) : $this->resolveDependency($name); // Выбор: Contract или Resolver
            } else {
                $args[] = $urlParams[$param->getName()] ?? ($param->isDefaultValueAvailable() ? $param->getDefaultValue() : null); // Данные URL
            }
        }
        return $args; // Возврат собранных аргументов
    }

    public function resolveDependency(string $className): mixed
    {
        if ($className === Request::class) return new Request(); // Создание Request
        if (is_subclass_of($className, \Requests\BaseRequest::class)) return new $className(); // Создание валидаторов
        return $this->autoWire($className); // Автоматическая сборка через конструктор
    }

    private function autoWire(string $concrete): object
    {
        $reflection = new ReflectionClass($concrete); // Рефлексия класса
        if (!$reflection->isInstantiable()) throw new Exception("Класс [{$concrete}] не инстанцируем"); // Проверка
        $constructor = $reflection->getConstructor(); // Получение конструктора
        if (!$constructor) return new $concrete(); // Создание без аргументов
        $dependencies = []; // Сборка зависимостей конструктора
        foreach ($constructor->getParameters() as $p) {
            $depType = $p->getType()->getName(); // Имя типа зависимости
            $dependencies[] = interface_exists($depType) ? Contract::make($depType) : $this->resolveDependency($depType); // Рекурсивный выбор
        }
        return $reflection->newInstanceArgs($dependencies); // Создание объекта с внедренными зависимостями
    }
}
