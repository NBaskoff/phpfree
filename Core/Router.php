<?php

namespace Core;

use Contracts\SessionContract;
use Exception;

/**
 * Класс управления маршрутизацией приложения
 */
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
     *
     * @param string $path Абсолютный путь к файлу конфигурации
     * @return void
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
     *
     * @param string $method HTTP метод (GET, POST и т.д.)
     * @param string $path URL путь из конфига
     * @param array $handler Массив с контроллером и методом
     * @return void
     */
    private function addRoute(string $method, string $path, array $handler): void
    {
        // Превращаем {id} или {slug} в именованные группы захвата (?P<name>[^/]+)
        $pattern = "@^" . preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path) . "$@";

        $this->routes[] = [
            'method'  => strtoupper($method),
            'pattern' => $pattern,
            'handler' => $handler,
            // Если в массиве передан 'csrf' => false, отключаем защиту, иначе — включена
            'csrf'    => $handler['csrf'] ?? true
        ];
    }

    /**
     * Основной метод запуска: находит соответствие URL и вызывает контроллер
     *
     * @return void
     */
    public function dispatch(): void
    {
        $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = strtoupper($_SERVER['REQUEST_METHOD']);

        foreach ($this->routes as $route) {
            // Проверяем совпадение HTTP-метода и регулярного выражения пути
            if ($route['method'] === $method && preg_match($route['pattern'], $url, $matches)) {

                // ПРОВЕРКА CSRF: только для опасных методов и если защита активна в конфиге
                if (in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH']) && $route['csrf'] === true) {
                    $this->checkCsrf();
                }

                [$controllerName, $methodName] = $route['handler'];

                // Оставляем в $matches только строковые ключи (наши параметры {id} и т.д.)
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                if (class_exists($controllerName)) {
                    // Создание экземпляра контроллера через new
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
     * Валидация CSRF токена через SessionContract
     *
     * @return void
     */
    private function checkCsrf(): void
    {
        /** @var SessionContract $session */
        $session = Contract::make(SessionContract::class);

        $tokenFromPost = $_POST['_csrf'] ?? '';
        $tokenFromSession = $session->get('_csrf');

        if (!$tokenFromSession || !hash_equals((string)$tokenFromSession, (string)$tokenFromPost)) {
            $this->abort(403, "Ошибка безопасности: неверный или просроченный CSRF-токен.");
        }
    }

    /**
     * Утилита для завершения работы с кодом ошибки и рендерингом шаблона
     *
     * @param int $code HTTP статус код
     * @param string $message Сообщение об ошибке
     * @return void
     */
    private function abort(int $code, string $message): void
    {
        http_response_code($code);

        try {
            // Пытаемся отрендерить красивую страницу ошибки templates/errors/404.php
            echo View::render("errors/{$code}", [
                'code'    => $code,
                'message' => $message,
                'title'   => "Ошибка $code"
            ]);
        } catch (Exception $e) {
            // Если шаблона для конкретной ошибки нет, выводим простой текст
            echo "<h1>$code</h1><p>$message</p>";
        }

        exit;
    }
}
