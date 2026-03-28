<?php

namespace Controllers;

use Core\View;

abstract class BaseController
{
    /**
     * Рендерит шаблон и возвращает HTML-код как строку
     */
    protected function render(string $template, array $data = []): string
    {
        return View::render($template, $data);
    }

    /**
     * Выводит отрендеренный HTML напрямую в браузер
     */
    protected function display(string $template, array $data = []): void
    {
        echo $this->render($template, $data);
    }

    /**
     * Отправляет JSON-ответ и завершает работу скрипта
     */
    protected function json($data, int $code = 200): void
    {
        if (ob_get_length()) ob_clean();

        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        exit;
    }

    /**
     * Перенаправляет пользователя на указанный URL
     */
    protected function redirect(string $url, int $code = 302): void
    {
        http_response_code($code);
        header("Location: $url");
        exit;
    }
}
