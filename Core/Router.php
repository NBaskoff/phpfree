<?php

namespace Core;

class Router
{
    private array $routes = [];

    public function loadRoutes(string $path): void
    {
        if (!file_exists($path)) {
            die("Ошибка Router: Файл конфигурации не найден: {$path}");
        }
        $routes = require $path;

        foreach ($routes as $path => $methods) {
            foreach ($methods as $method => $handler) {
                $this->addRoute($method, $path, $handler);
            }
        }
    }

    private function addRoute(string $method, string $path, array $handler): void
    {
        $pattern = "@^" . preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path) . "$@";

        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => $pattern,
            'handler' => $handler,
            // Если в массиве передан 'csrf' => false, отключаем защиту, иначе — включена
            'csrf' => $handler['csrf'] ?? true
        ];
    }

    public function dispatch(): void
    {
        $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = strtoupper($_SERVER['REQUEST_METHOD']);

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $url, $matches)) {

                // ПРОВЕРКА CSRF: только для опасных методов и если защита активна в конфиге
                if (in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH']) && $route['csrf'] === true) {
                    $this->checkCsrf();
                }

                [$controllerName, $methodName] = $route['handler'];
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                if (class_exists($controllerName)) {
                    $controller = new $controllerName();
                    if (method_exists($controller, $methodName)) {
                        call_user_func_array([$controller, $methodName], $params);
                        return;
                    }
                }

                $this->abort(500, "Метод $methodName не найден в $controllerName");
                return;
            }
        }

        $this->abort(404, "Страница не найдена");
    }

    /**
     * Валидация CSRF токена
     */
    private function checkCsrf(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $token = $_POST['_csrf'] ?? '';

        if (empty($_SESSION['_csrf']) || !hash_equals($_SESSION['_csrf'], $token)) {
            $this->abort(403, "Ошибка безопасности: неверный CSRF-токен.");
        }
    }

    private function abort(int $code, string $message): void
    {
        http_response_code($code);
        echo "<h1>$code</h1><p>$message</p>";
        exit;
    }
}
