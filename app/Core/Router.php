<?php

namespace Core;

use Exception;

/**
 * Ядро маршрутизации phpfree
 */
class Router
{
    private static ?self $instance = null;
    private static array $loadedFiles = []; // Список загруженных файлов роутов
    private array $routes = [];
    private array $namedRoutes = [];
    private Request $request;
    private Resolver $resolver;

    public function __construct()
    {
        $this->resolver = new Resolver();
        $this->request = new Request();
        self::$instance = $this;
    }

    /**
     * Статический доступ для глобального хелпера route()
     */
    public static function url(string $name, mixed $params = []): string
    {
        if (!self::$instance) {
            $files = !empty(self::$loadedFiles) ? implode("\n - ", self::$loadedFiles) : 'Файлы не загружены';
            throw new Exception("Роутер не инициализирован. Проверьте вызов loadRoutes().\nЗагруженные файлы:\n - {$files}");
        }
        return self::$instance->generate($name, $params);
    }

    /**
     * Генерация URL по имени с подстановкой параметров
     */
    public function generate(string $name, mixed $params = []): string
    {
        if (!isset($this->namedRoutes[$name])) {
            $files = implode("\n - ", self::$loadedFiles);
            throw new Exception(
                "Маршрут с именем '{$name}' не найден.\n" .
                "Проверьте наличие ключа 'name' в файлах:\n - {$files}"
            );
        }

        $path = $this->namedRoutes[$name];

        if (!is_array($params)) {
            if (preg_match('/\{([a-zA-Z0-9_]+)\}/', $path, $matches)) {
                $params = [$matches[1] => $params];
            } else {
                $params = [];
            }
        }

        foreach ($params as $key => $value) {
            $path = str_replace("{{$key}}", (string)$value, $path);
        }

        if (preg_match('/\{[a-zA-Z0-9_]+\}/', $path)) {
            throw new Exception("Недостаточно параметров для маршрута '{$name}': {$path}");
        }

        return $path ?: '/';
    }

    /**
     * Загрузка файла маршрутов
     */
    public function loadRoutes(string $prefix, string $path): void
    {
        if (!file_exists($path)) {
            die("Ошибка: Файл маршрутов не найден: {$path}");
        }

        // Запоминаем путь к файлу для отладки
        self::$loadedFiles[] = realpath($path);

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
            $fullPath = preg_replace('#/+#', '/', $prefix . '/' . ltrim($path, '/'));
            if ($fullPath !== '/') {
                $fullPath = rtrim($fullPath, '/');
            }

            $currentMiddleware = array_merge($middleware, $config['middleware'] ?? []);

            if (isset($config['name'])) {
                $this->namedRoutes[$config['name']] = $fullPath;
            }

            if (isset($config['routes'])) {
                $this->parseRoutes($config['routes'], $fullPath, $currentMiddleware);
                continue;
            }

            foreach ($config as $method => $handler) {
                if (in_array(strtoupper($method), ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'])) {
                    $this->addRoute($method, $fullPath, $handler, $currentMiddleware);
                }
            }
        }
    }

    private function addRoute(string $method, string $path, array $handler, array $middleware = []): void
    {
        $pattern = "@^" . preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path) . "$@";

        $this->routes[] = [
            'method'     => strtoupper($method),
            'pattern'    => $pattern,
            'handler'    => $handler,
            'middleware' => $middleware
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
