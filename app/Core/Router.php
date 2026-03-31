<?php

namespace Core;

use Exception;

/**
 * Ядро маршрутизации phpfree
 */
class Router
{
    private static ?self $instance = null; // Инстанс для работы хелпера route()
    private array $routes = [];            // Массив для сопоставления запросов
    private array $namedRoutes = [];       // Карта: [имя => путь]
    private Request $request;
    private Resolver $resolver;

    public function __construct()
    {
        $this->resolver = new Resolver();
        $this->request = new Request();
        self::$instance = $this; // Сохраняем текущий объект в статику
    }

    /**
     * Статический метод для вызова из глобальной функции route()
     */
    public static function url(string $name, array $params = []): string
    {
        if (!self::$instance) {
            throw new Exception("Роутер не инициализирован.");
        }
        return self::$instance->generate($name, $params);
    }

    /**
     * Генерация URL по имени с подстановкой параметров {id}
     */
    public function generate(string $name, array $params = []): string
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new Exception("Маршрут с именем '{$name}' не найден.");
        }

        $path = $this->namedRoutes[$name];

        // Подставляем параметры в плейсхолдеры
        foreach ($params as $key => $value) {
            $path = str_replace("{{$key}}", (string)$value, $path);
        }

        // Если после замены остались {..}, значит не все параметры переданы
        if (preg_match('/\{[a-zA-Z0-9_]+\}/', $path)) {
            throw new Exception("Недостаточно параметров для маршрута '{$name}': {$path}");
        }

        return $path ?: '/';
    }

    /**
     * Загрузка файла маршрутов с нормализацией префикса
     */
    public function loadRoutes(string $prefix, string $path): void
    {
        if (!file_exists($path)) {
            die("Ошибка: Файл маршрутов не найден: {$path}");
        }

        // Нормализация: превращаем 'admin', '/admin' или 'admin/' в '/admin'
        $prefix = trim($prefix, '/');
        $prefix = $prefix ? '/' . $prefix : '';

        $routes = require $path;
        $this->parseRoutes($routes, $prefix);
    }

    /**
     * Рекурсивный обход массива маршрутов
     */
    private function parseRoutes(array $routes, string $prefix = '', array $middleware = []): void
    {
        foreach ($routes as $path => $config) {
            // Склеиваем префиксы, удаляем дубли слешей
            $fullPath = preg_replace('#/+#', '/', $prefix . '/' . ltrim($path, '/'));
            if ($fullPath !== '/') {
                $fullPath = rtrim($fullPath, '/');
            }

            // Объединяем посредников (наследование от групп)
            $currentMiddleware = array_merge($middleware, $config['middleware'] ?? []);

            // Регистрируем имя маршрута, если оно задано
            if (isset($config['name'])) {
                $this->namedRoutes[$config['name']] = $fullPath;
            }

            // Если это вложенная группа
            if (isset($config['routes'])) {
                $this->parseRoutes($config['routes'], $fullPath, $currentMiddleware);
                continue;
            }

            // Регистрация стандартных HTTP-методов
            foreach ($config as $method => $handler) {
                if (in_array(strtoupper($method), ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'])) {
                    $this->addRoute($method, $fullPath, $handler, $currentMiddleware);
                }
            }
        }
    }

    /**
     * Добавление маршрута в общий стек для диспетчеризации
     */
    private function addRoute(string $method, string $path, array $handler, array $middleware = []): void
    {
        // Превращаем параметры {id} в регулярное выражение
        $pattern = "@^" . preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path) . "$@";

        $this->routes[] = [
            'method'     => strtoupper($method),
            'pattern'    => $pattern,
            'handler'    => $handler,
            'middleware' => $middleware
        ];
    }

    /**
     * Поиск и выполнение подходящего маршрута
     */
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

    /**
     * Запуск Middleware и контроллера через Resolver
     */
    private function execute(array $route, array $matches): void
    {
        foreach ($route['middleware'] as $mwClass) {
            $mwInstance = $this->resolver->resolveDependency($mwClass);
            $mwInstance->handle($this->request);
        }

        [$controllerName, $methodName] = $route['handler'];
        $urlParams = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

        if (!class_exists($controllerName)) {
            $this->abort(500, "Контроллер {$controllerName} не найден");
        }

        $controller = $this->resolver->resolveDependency($controllerName);
        $args = $this->resolver->resolveMethodArgs($controllerName, $methodName, $urlParams);

        $controller->{$methodName}(...$args);
    }

    /**
     * Обработка системных ошибок (404, 500)
     */
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
