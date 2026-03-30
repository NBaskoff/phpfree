<?php

namespace Requests;

use Exception;
use Core\Request;
use Contracts\RuleContract;

abstract class BaseRequest
{
    protected array $data = []; // Данные
    protected array $errors = []; // Ошибки

    public function __construct(Request $request) // Принимаем Request из Resolver
    {
        $this->prepareData($request); // Подготовка
        $this->validate(); // Валидация
        if (!empty($this->errors)) throw new Exception("Ошибка валидации: " . implode('; ', $this->errors)); // Выброс исключения
    }

    protected function prepareData(Request $request): void
    {
        $this->data = $this->sanitize([...$request->query, ...$request->post]); // Слияние и очистка
    }

    abstract protected function validate(): void; // Метод валидации

    protected function sanitize(mixed $data): mixed
    {
        if (is_array($data)) return array_map([$this, 'sanitize'], $data); // Рекурсия
        return is_string($data) ? htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8') : $data; // Очистка
    }

    protected function validateField(string $field, array $rules): void
    {
        $value = $this->get($field); // Значение
        foreach ($rules as $rule) {
            if ($rule instanceof RuleContract && !$rule($value)) $this->addError($rule->getMessage($field)); // Проверка правила
        }
    }

    protected function addError(string $msg): void { $this->errors[] = $msg; } // Добавление ошибки

    public function __invoke(): array { return $this->data; } // Данные через вызов объекта

    public function get(string $key, mixed $default = null): mixed { return $this->data[$key] ?? $default; } // Получение поля
}
