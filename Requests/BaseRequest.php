<?php

namespace Requests;

use Exception;
use Core\Validator;
use Contracts\RuleContract;

/**
 * Абстрактный базовый класс для всех запросов приложения
 */
abstract class BaseRequest
{
    /** @var array Очищенные входящие данные */
    protected array $data = [];

    /** @var array Список ошибок валидации */
    protected array $errors = [];

    /**
     * Конструктор собирает данные из глобальных массивов и запускает проверку
     *
     * @throws Exception
     */
    public function __construct()
    {
        // Сбор данных из GET и POST
        $this->data = array_merge($_GET, $_POST);

        // Автоматическая XSS-очистка через Validator
        $this->data = Validator::filter($this->data);

        // Запуск правил, определенных в дочернем классе
        $this->validate();

        if (!empty($this->errors)) {
            throw new Exception("Ошибка валидации: " . implode('; ', $this->errors));
        }
    }

    /**
     * Метод для определения правил в конкретных реквестах
     */
    abstract protected function validate(): void;

    /**
     * Позволяет получить массив данных, вызвав объект как функцию: $request()
     *
     * @return array
     */
    public function __invoke(): array
    {
        return $this->data;
    }

    /**
     * Проверяет поле набором объектов-валидаторов
     *
     * @param string $field Ключ поля (например, 'email')
     * @param array $rules Массив объектов RuleContract
     */
    protected function validateField(string $field, array $rules): void
    {
        $value = $this->get($field);

        foreach ($rules as $rule) {
            if ($rule instanceof RuleContract) {
                // Вызов правила через __invoke
                if (!$rule($value)) {
                    $this->addError($rule->getMessage($field));
                }
            }
        }
    }

    /**
     * Добавляет текст ошибки в общий список
     */
    protected function addError(string $msg): void
    {
        $this->errors[] = $msg;
    }

    /**
     * Получает значение из отфильтрованных данных
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }
}
