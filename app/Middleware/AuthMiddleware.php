<?php

namespace Middleware;

use Core\{Contract, Request}; // Ядро
use Contracts\SessionContract; // Контракт сессии

class AuthMiddleware extends BaseMiddleware
{
    public function handle(Request $request): void
    {
        $session = Contract::make(SessionContract::class); // Получаем сессию
        if (!$session->has('user_id')) { // Если не авторизован
            header('Location: /login'); // Редирект
            exit; // Остановка
        }
    }
}
