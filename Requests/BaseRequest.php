<?php

namespace Requests;

use Exception;
use Core\Validator;
use Core\Request;
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
     * @throws Exception
     */
    public function __construct()
    {
        $request = new Request();

        $this->data = array_merge($request->query, $request->post);
        $this->data = Validator::filter($this->data);

        $this->validate();

        if (!empty($this->errors)) {
            throw new Exception("Ошибка валидации: " . implode('; ', $this->errors));
        }
    }

    /**
     * @return void
     */
    abstract protected function validate(): void;

    /**
     * @return array
     */
    public function __invoke(): array
    {
        return $this->data;
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
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }
}
