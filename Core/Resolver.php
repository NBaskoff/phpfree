<?php

namespace Core;

use Contracts\DatabaseContract;
use ReflectionMethod;
use ReflectionClass;
use ReflectionNamedType;
use Exception;

/**
 * Класс для разрешения зависимостей контроллеров и экшенов
 */
class Resolver
{
    /**
     * @param Request $request
     */
    public function __construct(
        private Request $request
    ) {}

    /**
     * Собирает аргументы для вызова метода контроллера
     *
     * @param string $controller
     * @param string $method
     * @param array $urlParams
     * @return array
     * @throws Exception
     */
    public function resolveMethodArgs(string $controller, string $method, array $urlParams): array
    {
        $reflection = new ReflectionMethod($controller, $method);
        $args = [];

        foreach ($reflection->getParameters() as $param) {
            $type = $param->getType();
            $name = $param->getName();

            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $args[] = $this->resolveDependency($type->getName());
            } else {
                $args[] = $urlParams[$name] ?? ($param->isDefaultValueAvailable() ? $param->getDefaultValue() : null);
            }
        }

        return $args;
    }

    /**
     * Разрешает тип зависимости (Request, BaseRequest или Action)
     *
     * @param string $className
     * @return mixed
     */
    private function resolveDependency(string $className): mixed
    {
        if ($className === Request::class) {
            return $this->request;
        }

        if (is_subclass_of($className, \Requests\BaseRequest::class)) {
            return new $className();
        }

        if (str_contains($className, 'Actions')) {
            return $this->buildAction($className);
        }

        return null;
    }

    /**
     * Собирает Action, прокидывая репозиторий с базой данных
     *
     * @param string $actionClass
     * @return mixed
     * @throws Exception
     */
    private function buildAction(string $actionClass): mixed
    {
        $reflection = new ReflectionClass($actionClass);
        $constructor = $reflection->getConstructor();
        $args = [];

        if ($constructor) {
            foreach ($constructor->getParameters() as $param) {
                $type = $param->getType();
                if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                    $repoClass = $type->getName();
                    if (class_exists($repoClass)) {
                        $db = Contract::make(DatabaseContract::class);
                        $args[] = new $repoClass($db);
                    }
                }
            }
        }

        return new $actionClass(...$args);
    }
}
