<?php

namespace Middleware;

use Core\Request;
use Contracts\SessionContract;

/**
 * Посредник для проверки авторизации пользователя
 */
class AuthMiddleware extends BaseMiddleware
{
    /**
     * PHP 8.4: Автоматическое внедрение зависимости через конструктор.
     * Resolver сам найдет реализацию SessionContract в configs/contracts.php.
     */
    public function __construct(
        private readonly SessionContract $session
    ) {}

    /**
     * Проверка наличия активной сессии
     */
    public function handle(Request $request): void
    {
        // Если в сессии нет ID пользователя — отправляем на страницу логина по её имени
        if (!$this->session->has('user_id')) {
            // Используем именованный маршрут 'login'
            header('Location: ' . route('login'));
            exit;
        }
    }
}
