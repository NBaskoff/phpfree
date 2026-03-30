<?php

namespace Core;

use Exception;

class Router
{
    private array $routes = []; // Маршруты
    private Request $request; // Запрос
    private Resolver $resolver; // Резолвер

    public function __construct()
    {
        $this->resolver = Contract::getResolver(); // Получаем резолвер из контракта
        $this->request = $this->resolver->getRequest(); // Достаем запрос из резолвера
    }

    public function loadRoutes(string $prefix, string $path): void
    {
        if (!file_exists($path)) die("Ошибка: Файл маршрутов не найден"); // Проверка
        $routes = require $path; // Загрузка
        $prefix = trim($prefix, '/') ? '/' . trim($prefix, '/') : ''; // Префикс
        foreach ($routes as $url => $methods) {
            $fullUrl = preg_replace('#/+#', '/', $prefix . '/' . ltrim($url, '/')); // Путь
            if ($fullUrl !== '/') $fullUrl = rtrim($fullUrl, '/'); // Очистка
            foreach ($methods as $m => $h) $this->addRoute($m, $fullUrl, $h); // Регистрация
        }
    }

    private function addRoute(string $method, string $path, array $handler): void
    {
        $pattern = "@^" . preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path) . "$@"; // Паттерн
        $this->routes[] = ['method' => strtoupper($method), 'pattern' => $pattern, 'handler' => $handler]; // В массив
    }

    public function dispatch(): void
    {
        foreach ($this->routes as $route) {
            if ($route['method'] === $this->request->method && preg_match($route['pattern'], $this->request->uri, $matches)) {
                $this->execute($route, $matches); // Запуск
                return;
            }
        }
        $this->abort(404, "Page Not Found"); // 404
    }

    private function execute(array $route, array $matches): void
    {
        [$controllerName, $methodName] = $route['handler']; // Класс и метод
        $urlParams = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY); // Параметры URL
        $controller = $this->resolver->resolveDependency($controllerName); // Создание контроллера
        $args = $this->resolver->resolveMethodArgs($controllerName, $methodName, $urlParams); // Аргументы метода
        $controller->{$methodName}(...$args); // Вызов
    }

    private function abort(int $code, string $message): void
    {
        http_response_code($code); // Код
        try { echo View::render("errors/{$code}", ['message' => $message]); } // Вьюха ошибки
        catch (Exception) { echo "<h1>{$code}</h1>"; } // Фоллбэк
        exit; // Стоп
    }
}
