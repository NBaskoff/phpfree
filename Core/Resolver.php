<?php

namespace Core;

use ReflectionMethod;
use ReflectionClass;
use ReflectionNamedType;
use Exception;

class Resolver
{
    public function __construct(private readonly Request $request) {} // Внедрение Request (PHP 8.4)

    public function getRequest(): Request { return $this->request; } // Получение запроса для Роутера

    public function resolveMethodArgs(string $controller, string $method, array $urlParams): array
    {
        $reflection = new ReflectionMethod($controller, $method); // Рефлексия метода
        $args = []; // Массив аргументов
        foreach ($reflection->getParameters() as $param) {
            $type = $param->getType(); // Тип параметра
            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) $args[] = Contract::make($type->getName()); // Разрешение через Contract
            else $args[] = $urlParams[$param->getName()] ?? ($param->isDefaultValueAvailable() ? $param->getDefaultValue() : null); // Значение или дефолт
        }
        return $args; // Возврат аргументов
    }

    public function resolveDependency(string $className): mixed
    {
        if ($className === Request::class) return $this->request; // Прямая отдача Request
        if (is_subclass_of($className, \Requests\BaseRequest::class)) return new $className(); // Создание BaseRequest
        if (str_contains($className, 'Actions')) return $this->buildAction($className); // Сборка экшенов
        return $this->autoWire($className); // Универсальный Auto-wiring
    }

    private function autoWire(string $concrete): object
    {
        $reflection = new ReflectionClass($concrete); // Рефлексия класса
        if (!$reflection->isInstantiable()) throw new Exception("Класс [{$concrete}] не инстанцируем"); // Проверка
        $constructor = $reflection->getConstructor(); // Получение конструктора
        if (!$constructor) return new $concrete(); // Создание без конструктора
        $dependencies = array_map(fn($p) => Contract::make($p->getType()->getName()), $constructor->getParameters()); // Рекурсивная сборка зависимостей
        return $reflection->newInstanceArgs($dependencies); // Создание с аргументами
    }

    private function buildAction(string $actionClass): mixed
    {
        $reflection = new ReflectionClass($actionClass); // Рефлексия экшена
        $constructor = $reflection->getConstructor(); // Конструктор экшена
        $args = []; // Аргументы
        if ($constructor) foreach ($constructor->getParameters() as $p) $args[] = Contract::make($p->getType()->getName()); // Сборка аргументов через Contract
        return new $actionClass(...$args); // Возврат экшена
    }
}
