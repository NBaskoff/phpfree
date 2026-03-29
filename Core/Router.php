<?php

namespace Core;

use Contracts\DatabaseContract;
use Repositories\UserRepository;
use ReflectionMethod;
use Exception;

/**
 * Класс управления маршрутизацией приложения
 */
class Router
{
    /** @var array Реестр маршрутов */
    private array $routes = [];

    /**
     * Загружает маршруты из файла с поддержкой префикса
     *
     * @param string $prefix Префикс для URL
     * @param string $path Путь к файлу маршрутов
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
     * Регистрация маршрута с регулярным выражением
     *
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
     * Основной метод обработки запроса и вызова контроллера
     *
     * @return void
     */
    public function dispatch(): void
    {
        $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = strtoupper($_SERVER['REQUEST_METHOD']);

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $url, $matches)) {

                if (in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH']) && $route['csrf'] === true) {
                    $this->checkCsrf();
                }

                [$controllerName, $methodName] = $route['handler'];
                $urlParams = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                if (class_exists($controllerName)) {
                    $controller = new $controllerName();

                    if (method_exists($controller, $methodName)) {
                        $reflection = new ReflectionMethod($controllerName, $methodName);
                        $methodArgs = [];

                        foreach ($reflection->getParameters() as $parameter) {
                            $type = $parameter->getType();
                            $name = $parameter->getName();

                            if ($type && !$type->isBuiltin()) {
                                $className = $type->getName();

                                if (is_subclass_of($className, \Requests\BaseRequest::class)) {
                                    $methodArgs[] = new $className();
                                }
                                elseif (str_contains($className, 'Actions')) {
                                    $db = Contract::make(DatabaseContract::class);
                                    $userRepo = new UserRepository($db);
                                    $methodArgs[] = new $className($userRepo);
                                }
                            }
                            elseif (isset($urlParams[$name])) {
                                $methodArgs[] = $urlParams[$name];
                            }
                        }

                        call_user_func_array([$controller, $methodName], $methodArgs);
                        return;
                    }
                }

                $this->abort(500, "Method $methodName not found in $controllerName");
                return;
            }
        }

        $this->abort(404, "Page not found");
    }

    /**
     * Валидация CSRF токена
     *
     * @return void
     */
    private function checkCsrf(): void
    {
        $session = Contract::make(\Contracts\SessionContract::class);
        $token = $_POST['_csrf'] ?? '';
        $sessionToken = $session->get('_csrf');

        if (!$sessionToken || !hash_equals((string)$sessionToken, (string)$token)) {
            $this->abort(403, "Ошибка безопасности: неверный CSRF-токен.");
        }
    }

    /**
     * Остановка выполнения с HTTP кодом ошибки
     *
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
