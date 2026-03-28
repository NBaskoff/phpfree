<?php

namespace Controllers;

use Core\View;
use Exception;
use JetBrains\PhpStorm\NoReturn;
use JsonException;

abstract class BaseController
{
    /**
     * Рендерит шаблон и возвращает HTML-код как строку
     * @throws Exception
     */
    protected function render(string $template, array $data = []): string
    {
        return View::render($template, $data);
    }

    /**
     * Выводит отрендеренный HTML и возвращает его (для поддержки return в контроллере)
     * @throws Exception
     */
    protected function display(string $template, array $data = []): string
    {
        $html = $this->render($template, $data);
        echo $html;
        return $html;
    }

    /**
     * Отправляет JSON-ответ и завершает работу
     * @throws JsonException
     */
    #[NoReturn]
    protected function json($data, int $code = 200): void
    {
        if (ob_get_length()) ob_clean();
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        exit;
    }

    /**
     * Перенаправляет пользователя и завершает работу
     */
    #[NoReturn]
    protected function redirect(string $url, int $code = 302): void
    {
        http_response_code($code);
        header("Location: $url");
        exit;
    }
}
