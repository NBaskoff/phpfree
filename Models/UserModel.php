<?php

namespace Models;

/**
 * Модель данных пользователя
 */
class UserModel extends BaseModel
{
    public int $id;
    public string $name;
    public string $email;
    public ?string $created_at = null;
    public ?string $updated_at = null;

    /**
     * Массив объектов или строк с названиями ролей (заполняется отдельно)
     * @var array
     */
    public array $roles = [];

    /**
     * Проверяет, назначена ли пользователю конкретная роль
     *
     * @param string $slug Код роли (admin, user, editor)
     * @return bool
     */
    public function hasRole(string $slug): bool
    {
        return in_array($slug, array_column($this->roles, 'slug'));
    }

    /**
     * Форматирует дату регистрации
     *
     * @return string
     */
    public function getFormattedDate(): string
    {
        return $this->created_at ? date('d.m.Y H:i', strtotime($this->created_at)) : 'Не указана';
    }
}
