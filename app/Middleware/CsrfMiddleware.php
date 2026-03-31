<?php

namespace Middleware;

use Core\{Request, Contract}; // Ядро
use Contracts\SessionContract; // Сессия

class CsrfMiddleware extends BaseMiddleware
{
    public function handle(Request $request): void
    {
        // PHP 8.4: Проверяем только методы, изменяющие состояние
        if (in_array($request->method, ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            $session = Contract::make(SessionContract::class); // Получаем синглтон сессии
            $token = $request->input('_csrf'); // Ищем токен в теле запроса

            // Сравнение хешей для защиты от атак по времени
            if (!$token || !hash_equals((string)$session->get('_csrf', ''), (string)$token)) {
                http_response_code(419); // Статус Page Expired
                die("Ошибка безопасности: неверный CSRF-токен."); // Остановка
            }
        }
    }
}
