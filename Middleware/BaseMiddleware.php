<?php

namespace Middleware;

use Core\Request; // Зависимость от запроса

abstract class BaseMiddleware
{
    /**
     * Основной метод проверки
     */
    abstract public function handle(Request $request): void; // Обязателен для реализации
}
