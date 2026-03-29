<?php

namespace Core;

use Contracts\DatabaseContract;
use ReflectionMethod;
use ReflectionClass;
use Exception;

/**
 * Класс управления маршрутизацией приложения
 */
class Router
{
    /** @var array Реестр маршрутов */
    private array $routes = [];

    /** @var Request Объект текущего запроса */
    private Request $request;

    /**
     * @param Request|null $request
     */
    public function __construct(?Request $request = null)
    {
        $this->request = $request ?? new Request();
    }

    /**
     * @param string $prefix
     * @param string $path
     * @return void
     */
    public function loadRoutes(string $prefix, string $path): void
    {
        if (!file_exists($path)) {
            die("Ошибка Router: Файл конфигурации не найден: {$path}");
        }

        $routes = require $path;
        $prefix = trim($prefix, '/');
        $prefix = $prefix ? '/' . $prefix : '';

        foreach ($routes as $url => $methods) {
            $cleanUrl = ltrim($url, '/');
            $fullUrl = preg_replace('#/+#', '/', $prefix . '/' . $cleanUrl);

            if ($fullUrl !== '/') {
                $fullUrl = rtrim($fullUrl, '/');
            }

            foreach ($methods as $method => $handler) {
                $this->addRoute($method, $fullUrl, $handler);
            }
        }
    }

    /**
     * @param string $method
     * @param string $path
     * @param array $handler
     * @return void
     */
    private function addRoute(string $method, string $path, array $handler): void
    {
        $pattern = "@^" . preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path) . "$@";

        $this->routes[] = [
            'method'  => strtoupper($method),
            'pattern' => $pattern,
            'handler' => $handler
        ];
    }

    /**
     * @return void
     */
    public function dispatch(): void
    {
        $url = $this->request->uri;
        $method = $this->request->method;

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $url, $matches)) {
                $this->execute($route, $matches);
                return;
            }
        }

        $this->abort(404, "Page not found");
    }

    /**
     * @param array $route
     * @param array $matches
     * @return void
     */
    private function execute(array $route, array $matches): void
    {
        [$controllerName, $methodName] = $route['handler'];
        $urlParams = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

        if (!class_exists($controllerName)) {
            $this->abort(500, "Controller $controllerName not found");
        }

        $controller = new $controllerName();
        $args = $this->resolveArgs($controllerName, $methodName, $urlParams);

        $controller->{$methodName}(...$args);
    }

    /**
     * @param string $controller
     * @param string $method
     * @param array $urlParams
     * @return array
     */
    private function resolveArgs(string $controller, string $method, array $urlParams): array
    {
        $reflection = new ReflectionMethod($controller, $method);
        $args = [];

        foreach ($reflection->getParameters() as $param) {
            $type = $param->getType();
            $name = $param->getName();
            $className = ($type && !$type->isBuiltin()) ? $type->getName() : null;

            if ($className) {
                $args[] = $this->resolveDependency($className);
            } else {
                $args[] = $urlParams[$name] ?? ($param->isDefaultValueAvailable() ? $param->getDefaultValue() : null);
            }
        }

        return $args;
    }

    /**
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
     * @param string $actionClass
     * @return mixed
     */
    private function buildAction(string $actionClass): mixed
    {
        $reflection = new ReflectionClass($actionClass);
        $constructor = $reflection->getConstructor();
        $args = [];

        if ($constructor) {
            foreach ($constructor->getParameters() as $param) {
                $type = $param->getType();
                $repoClass = ($type && !$type->isBuiltin()) ? $type->getName() : null;

                if ($repoClass) {
                    $db = Contract::make(DatabaseContract::class);
                    $args[] = new $repoClass($db);
                }
            }
        }

        return new $actionClass(...$args);
    }

    /**
     * @param int $code
     * @param string $message
     * @return void
     */
    private function abort(int $code, string $message): void
    {
        http_response_code($code);
        try {
            echo View::render("errors/{$code}", ['message' => $message]);
        } catch (Exception) {
            echo "<h1>$code</h1><p>$message</p>";
        }
        exit;
    }
}
