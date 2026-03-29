<?php

namespace Core;

use Contracts\DatabaseContract;
use Contracts\SessionContract;
use Repositories\UserRepository;
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
            'handler' => $handler,
            'csrf'    => $handler['csrf'] ?? true
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
                $this->processRoute($route, $matches);
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
    private function processRoute(array $route, array $matches): void
    {
        if (in_array($this->request->method, ['POST', 'PUT', 'DELETE', 'PATCH']) && $route['csrf'] === true) {
            $this->checkCsrf();
        }

        [$controllerName, $methodName] = $route['handler'];
        $urlParams = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

        if (!class_exists($controllerName)) {
            $this->abort(500, "Controller $controllerName not found");
        }

        $controller = new $controllerName();

        if (!method_exists($controller, $methodName)) {
            $this->abort(500, "Method $methodName not found in $controllerName");
        }

        $args = $this->resolveMethodArgs($controllerName, $methodName, $urlParams);
        call_user_func_array([$controller, $methodName], $args);
    }

    /**
     * @param string $controller
     * @param string $method
     * @param array $urlParams
     * @return array
     */
    private function resolveMethodArgs(string $controller, string $method, array $urlParams): array
    {
        $reflection = new ReflectionMethod($controller, $method);
        $methodArgs = [];

        foreach ($reflection->getParameters() as $parameter) {
            $type = $parameter->getType();
            $name = $parameter->getName();
            $className = ($type && !$type->isBuiltin()) ? $type->getName() : null;

            if ($className) {
                $methodArgs[] = $this->resolveClassDependency($className);
            } elseif (isset($urlParams[$name])) {
                $methodArgs[] = $urlParams[$name];
            } else {
                $methodArgs[] = $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null;
            }
        }

        return $methodArgs;
    }

    /**
     * @param string $className
     * @return mixed
     */
    private function resolveClassDependency(string $className): mixed
    {
        if ($className === Request::class) {
            return $this->request;
        }

        if (is_subclass_of($className, \Requests\BaseRequest::class)) {
            return new $className();
        }

        if (str_contains($className, 'Actions')) {
            return $this->resolveAction($className);
        }

        return null;
    }

    /**
     * @param string $actionClass
     * @return mixed
     */
    private function resolveAction(string $actionClass): mixed
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
     * @return void
     */
    private function checkCsrf(): void
    {
        $session = Contract::make(SessionContract::class);
        $token = $this->request->post['_csrf'] ?? '';
        $sessionToken = $session->get('_csrf');

        if (!$sessionToken || !hash_equals((string)$sessionToken, (string)$token)) {
            $this->abort(403, "Ошибка безопасности: неверный CSRF-токен.");
        }
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
            echo View::render("errors/{$code}", [
                'code' => $code,
                'message' => $message,
                'title' => "Ошибка $code"
            ]);
        } catch (Exception $e) {
            echo "<h1>$code</h1><p>$message</p>";
        }
        exit;
    }
}
