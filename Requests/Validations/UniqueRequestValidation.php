<?php

namespace Requests\Validations;

use Contracts\RuleContract;
use Contracts\DatabaseContract;

class UniqueRequestValidation implements RuleContract
{
    public function __construct(
        private DatabaseContract $db,
        private string $table,
        private string $column,
        private mixed $ignoreId = null
    ) {}

    /**
     * Теперь никакой SQL-грязи, только контракт
     */
    public function __invoke(mixed $value): bool
    {
        // Если запись существует — валидация провалена (возвращаем false)
        return !$this->db->exists($this->table, $this->column, $value, $this->ignoreId);
    }

    public function getMessage(string $field): string
    {
        return "Значение поля {$field} уже используется.";
    }
}
