<?php

namespace Core;

use Exception;

abstract class Request
{
    protected array $data = [];
    protected array $errors = [];

    public function __construct()
    {
        $this->data = array_merge($_GET, $_POST);
        $this->data = Validator::filter($this->data);
        $this->validate();

        if (!empty($this->errors)) {
            throw new Exception("Validation failed: " . implode(', ', $this->errors));
        }
    }

    abstract protected function validate(): void;

    /**
     * Возвращает чистые данные при вызове $request()
     */
    public function __invoke(): array
    {
        return $this->data;
    }

    protected function addError(string $msg): void { $this->errors[] = $msg; }
    public function get(string $key, mixed $default = null): mixed { return $this->data[$key] ?? $default; }
}
