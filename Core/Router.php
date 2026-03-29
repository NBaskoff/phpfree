<?php

namespace Core;

use Exception;

/**
 * Класс управления маршрутизацией
 */
class Router
{
    private array $routes = [];
    private Request $request;
    private Resolver $resolver;

    public function __construct(?Request $request = null)
    {
        $this->request = $request ?? new Request();
        $this->resolver = new Resolver($this->request);
    }

    public function loadRoutes(string $prefix, string $path): void
    {
        if (!file_exists($path)) {
            die("Ошибка: Файл маршрутов не найден: {$path}");
        }

        $routes = require $path;
        $prefix = trim($prefix, '/') ? '/' . trim($prefix, '/') : '';

        foreach ($routes as $url => $methods) {
            $fullUrl = preg_replace('#/+#', '/', $prefix . '/' . ltrim($url, '/'));
            if ($fullUrl !== '/') $fullUrl = rtrim($fullUrl, '/');

            foreach ($methods as $method => $handler) {
                $this->addRoute($method, $fullUrl, $handler);
            }
        }
    }

    private function addRoute(string $method, string $path, array $handler): void
    {
        $pattern = "@^" . preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path) . "$@";
        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => $pattern,
            'handler' => $handler
        ];
    }

    public function dispatch(): void
    {
        foreach ($this->routes as $route) {
            if ($route['method'] === $this->request->method && preg_match($route['pattern'], $this->request->uri, $matches)) {
                $this->execute($route, $matches);
                return;
            }
        }
        $this->abort(404, "Страница не найдена");
    }

    private function execute(array $route, array $matches): void
    {
        [$controllerName, $methodName] = $route['handler'];
        $urlParams = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

        if (!class_exists($controllerName)) {
            $this->abort(500, "Контроллер {$controllerName} не найден");
        }

        $controller = new $controllerName();
        $args = $this->resolver->resolveMethodArgs($controllerName, $methodName, $urlParams);

        $controller->{$methodName}(...$args);
    }

    private function abort(int $code, string $message): void
    {
        http_response_code($code);
        try {
            echo View::render("errors/{$code}", ['message' => $message]);
        } catch (Exception) {
            echo "<h1>{$code}</h1><p>{$message}</p>";
        }
        exit;
    }
}
