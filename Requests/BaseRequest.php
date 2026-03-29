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
     * @param string $field
     * @param array $rules
     * @return void
     */
    protected function validateField(string $field, array $rules): void
    {
        $value = $this->get($field);

        foreach ($rules as $rule) {
            if ($rule instanceof RuleContract) {
                if (!$rule($value)) {
                    $this->addError($rule->getMessage($field));
                }
            }
        }
    }

    /**
     * @param string $msg
     * @return void
     */
    protected function addError(string $msg): void
    {
        $this->errors[] = $msg;
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
