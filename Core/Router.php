<?php

namespace Core;

use Exception; // Исключения

class Router
{
    private array $routes = []; // Массив откомпилированных маршрутов
    private Request $request; // Объект запроса
    private Resolver $resolver; // Объект резолвера

    public function __construct()
    {
        $this->resolver = new Resolver(); // Прямое создание резолвера
        $this->request = new Request(); // Самостоятельное создание запроса
    }

    public function loadRoutes(string $prefix, string $path): void
    {
        if (!file_exists($path)) die("Ошибка: Файл маршрутов не найден: {$path}"); // Проверка файла
        $routes = require $path; // Загрузка массива из файла
        $this->parseRoutes($routes, $prefix); // Запуск рекурсивного парсинга
    }

    private function parseRoutes(array $routes, string $prefix = '', array $middleware = []): void
    {
        foreach ($routes as $path => $config) {
            $fullPath = preg_replace('#/+#', '/', $prefix . '/' . ltrim($path, '/')); // Склейка путей
            if ($fullPath !== '/') $fullPath = rtrim($fullPath, '/'); // Удаление конечного слэша

            $currentMiddleware = array_merge($middleware, $config['middleware'] ?? []); // Объединение посредников

            if (isset($config['routes'])) {
                $this->parseRoutes($config['routes'], $fullPath, $currentMiddleware); // Рекурсия для групп
                continue; // Переход к следующему элементу
            }

            foreach ($config as $method => $handler) {
                if (in_array(strtoupper($method), ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'])) {
                    $this->addRoute($method, $fullPath, $handler, $currentMiddleware); // Регистрация маршрута
                }
            }
        }
    }

    private function addRoute(string $method, string $path, array $handler, array $middleware = []): void
    {
        $pattern = "@^" . preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path) . "$@"; // Регулярка для параметров
        $this->routes[] = [
            'method'     => strtoupper($method),
            'pattern'    => $pattern,
            'handler'    => $handler,
            'middleware' => $middleware // Привязка Middleware к маршруту
        ];
    }

    public function dispatch(): void
    {
        foreach ($this->routes as $route) {
            if ($route['method'] === $this->request->method && preg_match($route['pattern'], $this->request->uri, $matches)) {
                $this->execute($route, $matches); // Выполнение найденного маршрута
                return;
            }
        }
        $this->abort(404, "Страница не найдена"); // Ошибка 404
    }

    private function execute(array $route, array $matches): void
    {
        foreach ($route['middleware'] as $mwClass) {
            $mwInstance = $this->resolver->resolveDependency($mwClass); // Создание через Resolver
            $mwInstance->handle($this->request); // Запуск метода handle посредника
        }

        [$controllerName, $methodName] = $route['handler']; // Извлечение контроллера и метода
        $urlParams = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY); // Сбор параметров из URL

        if (!class_exists($controllerName)) $this->abort(500, "Контроллер {$controllerName} не найден"); // Проверка

        $controller = $this->resolver->resolveDependency($controllerName); // Инстанциация контроллера
        $args = $this->resolver->resolveMethodArgs($controllerName, $methodName, $urlParams); // Сбор аргументов метода

        $controller->{$methodName}(...$args); // Вызов экшена
    }

    private function abort(int $code, string $message): void
    {
        http_response_code($code); // Установка HTTP кода
        try { echo View::render("errors/{$code}", ['message' => $message]); } // Попытка рендера страницы ошибки
        catch (Exception) { echo "<h1>{$code}</h1><p>{$message}</p>"; } // Запасной вариант вывода
        exit; // Остановка скрипта
    }
}
