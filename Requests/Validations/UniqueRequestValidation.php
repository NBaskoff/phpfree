<?php

namespace Requests\Validations;

use Contracts\RuleContract;
use Contracts\DatabaseContract;

/**
 * Правило проверки уникальности значения в базе данных
 */
class UniqueRequestValidation implements RuleContract
{
    /**
     * @param DatabaseContract $db Экземпляр БД
     * @param string $table Имя таблицы
     * @param string $column Имя колонки
     * @param mixed $ignoreId ID записи, которую нужно игнорировать (для обновления)
     */
    public function __construct(
        private DatabaseContract $db,
        private string $table,
        private string $column,
        private mixed $ignoreId = null
    ) {}

    /**
     * Проверка: если запись найдена — возвращаем false
     */
    public function __invoke(mixed $value): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE {$this->column} = :val";
        $params = ['val' => $value];

        if ($this->ignoreId) {
            $sql .= " AND id != :id";
            $params['id'] = $this->ignoreId;
        }

        $result = $this->db->row($sql, $params);

        return (int)($result['count'] ?? 0) === 0;
    }

    public function getMessage(string $field): string
    {
        return "Значение поля {$field} уже занято.";
    }
}
