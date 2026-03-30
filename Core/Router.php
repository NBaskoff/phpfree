<?php

namespace Core;

use Exception;

class Router
{
    private array $routes = []; // Массив маршрутов
    private Request $request; // Объект запроса
    private Resolver $resolver; // Объект резолвера

    public function __construct() // Пустой конструктор (без аргументов)
    {
        $this->request = new Request(); // Прямое создание запроса
        $this->resolver = new Resolver($this->request); // Прямое создание резолвера
    }

    public function loadRoutes(string $prefix, string $path): void
    {
        if (!file_exists($path)) die("Ошибка: Файл маршрутов не найден: {$path}"); // Проверка файла
        $routes = require $path; // Загрузка массива
        $prefix = trim($prefix, '/') ? '/' . trim($prefix, '/') : ''; // Формирование префикса
        foreach ($routes as $url => $methods) {
            $fullUrl = preg_replace('#/+#', '/', $prefix . '/' . ltrim($url, '/')); // Чистка URL
            if ($fullUrl !== '/') $fullUrl = rtrim($fullUrl, '/'); // Удаление слеша
            foreach ($methods as $method => $handler) $this->addRoute($method, $fullUrl, $handler); // Регистрация
        }
    }

    private function addRoute(string $method, string $path, array $handler): void
    {
        $pattern = "@^" . preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path) . "$@"; // Регулярка
        $this->routes[] = ['method' => strtoupper($method), 'pattern' => $pattern, 'handler' => $handler]; // Сохранение
    }

    public function dispatch(): void
    {
        foreach ($this->routes as $route) {
            if ($route['method'] === $this->request->method && preg_match($route['pattern'], $this->request->uri, $matches)) {
                $this->execute($route, $matches); // Запуск экшена
                return;
            }
        }
        $this->abort(404, "Страница не найдена"); // Ошибка 404
    }

    private function execute(array $route, array $matches): void
    {
        [$controllerName, $methodName] = $route['handler']; // Разбор хендлера
        $urlParams = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY); // Фильтр параметров
        if (!class_exists($controllerName)) $this->abort(500, "Контроллер {$controllerName} не найден"); // Проверка класса
        $controller = $this->resolver->resolveDependency($controllerName); // Создание контроллера через Resolver напрямую
        $args = $this->resolver->resolveMethodArgs($controllerName, $methodName, $urlParams); // Разрешение аргументов через Resolver
        $controller->{$methodName}(...$args); // Вызов метода
    }

    private function abort(int $code, string $message): void
    {
        http_response_code($code); // Код ответа
        try { echo View::render("errors/{$code}", ['message' => $message]); } // Рендер ошибки
        catch (Exception) { echo "<h1>{$code}</h1><p>{$message}</p>"; } // Запасной вывод
        exit; // Остановка
    }
}
