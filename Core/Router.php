<?php

namespace Core;

class Router
{
    /**
     * Список всех зарегистрированных маршрутов
     * @var array
     */
    private array $routes = [];

    /**
     * Загружает маршруты из внешнего массива конфигурации.
     * Ожидает структуру: [ 'url' => [ 'METHOD' => [Controller::class, 'method'] ] ]
     */
    public function loadRoutes(string $path): void
    {
        if (!file_exists($path)) {
            die("Ошибка Router: Файл конфигурации не найден по пути: {$path}");
        }
        $routes = require $path;
        if (!is_array($routes)) {
            die("Ошибка Router: Файл конфигурации должен возвращать массив.");
        }
        foreach ($routes as $path => $methods) {
            foreach ($methods as $method => $handler) {
                $this->addRoute($method, $path, $handler);
            }
        }
    }

    /**
     * Внутренний метод для преобразования пути в регулярное выражение
     */
    private function addRoute(string $method, string $path, array $handler): void
    {
        // Превращаем {id} или {slug} в именованные группы захвата (?P<name>[^/]+)
        $pattern = "@^" . preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path) . "$@";

        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => $pattern,
            'handler' => $handler
        ];
    }

    /**
     * Основной метод запуска: находит соответствие URL и вызывает контроллер
     */
    public function dispatch(): void
    {
        $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];

        foreach ($this->routes as $route) {
            // Проверяем совпадение HTTP-метода и регулярного выражения пути
            if ($route['method'] === $method && preg_match($route['pattern'], $url, $matches)) {

                [$controllerName, $methodName] = $route['handler'];

                // Оставляем в $matches только строковые ключи (наши параметры {id} и т.д.)
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                if (class_exists($controllerName)) {
                    $controller = new $controllerName();

                    if (method_exists($controller, $methodName)) {
                        // Вызываем метод контроллера, передавая параметры как аргументы
                        call_user_func_array([$controller, $methodName], $params);
                        return;
                    }
                }

                $this->abort(500, "Method $methodName not found in $controllerName");
                return;
            }
        }

        // Если совпадений не найдено
        $this->abort(404, "Page not found");
    }

    /**
     * Утилита для завершения работы с кодом ошибки
     */
    private function abort(int $code, string $message): void
    {
        http_response_code($code);
        echo "<h1>$code</h1><p>$message</p>";
        exit;
    }
}