<?php

namespace Requests;

use Exception;
use Core\Request;
use Contracts\RuleContract;

/**
 * Абстрактный базовый класс для всех запросов приложения
 */
abstract class BaseRequest
{
    /** @var array */
    protected array $data = [];

    /** @var array */
    protected array $errors = [];

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->prepareData(new Request());
        $this->validate();

        if (!empty($this->errors)) {
            throw new Exception("Ошибка валидации: " . implode('; ', $this->errors));
        }
    }

    /**
     * @param Request $request
     * @return void
     */
    protected function prepareData(Request $request): void
    {
        $raw = array_merge($request->query, $request->post);
        $this->data = $this->sanitize($raw);
    }

    /**
     * Абстрактный метод: обязан быть реализован в наследнике.
     * Не может быть private.
     *
     * @return void
     */
    abstract protected function validate(): void;

    /**
     * @param mixed $data
     * @return mixed
     */
    protected function sanitize(mixed $data): mixed
    {
        if (is_array($data)) {
            return array_map([$this, 'sanitize'], $data);
        }

        return is_string($data) ? htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8') : $data;
    }

    /**
     * @return array
     */
    public function __invoke(): array
    {
        return $this->data;
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }
}
