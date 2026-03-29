<?php

namespace ViewHelpers;

class CsrfViewHelper
{
    /**
     * Генерирует HTML-код скрытого поля с CSRF-токеном
     */
    public function __invoke(): string
    {
        // Стартуем сессию, если она еще не запущена
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Генерируем токен один раз за сессию, если его еще нет
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }

        $token = $_SESSION['_csrf'];

        return "<input type=\"hidden\" name=\"_csrf\" value=\"{$token}\">";
    }
}
