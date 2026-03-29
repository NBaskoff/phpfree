<?php

namespace Core;

use Contracts\DatabaseContract;
use Contracts\SessionContract;
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
                            $className = ($type && !$type->isBuiltin()) ? $type->getName() : null;

                            if ($className) {
                                if ($className === Request::class) {
                                    $methodArgs[] = $this->request;
                                }
                                elseif (is_subclass_of($className, \Requests\BaseRequest::class)) {
                                    $methodArgs[] = new $className();
                                }
                                /** Универсальная сборка Action */
                                elseif (str_contains($className, 'Actions')) {
                                    $actionReflection = new \ReflectionClass($className);
                                    $constructor = $actionReflection->getConstructor();
                                    $actionArgs = [];

                                    if ($constructor) {
                                        foreach ($constructor->getParameters() as $constructorParam) {
                                            $paramType = $constructorParam->getType();
                                            $repoClassName = ($paramType && !$paramType->isBuiltin()) ? $paramType->getName() : null;

                                            if ($repoClassName) {
                                                $db = Contract::make(DatabaseContract::class);
                                                $actionArgs[] = new $repoClassName($db);
                                            }
                                        }
                                    }
                                    $methodArgs[] = new $className(...$actionArgs);
                                } else {
                                    $methodArgs[] = null;
                                }
                            }
                            elseif (isset($urlParams[$name])) {
                                $methodArgs[] = $urlParams[$name];
                            }
                            else {
                                $methodArgs[] = $parameter->isDefaultValueAvailable()
                                    ? $parameter->getDefaultValue()
                                    : null;
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
