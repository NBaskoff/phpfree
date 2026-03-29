<?php

namespace Models;

/**
 * Модель данных пользователя
 */
class UserModel extends BaseModel
{
    /** @var int Идентификатор пользователя */
    public int $id;

    /** @var string Имя пользователя */
    public string $name;

    /** @var string Электронная почта */
    public string $email;

    /** @var string|null Дата регистрации */
    public ?string $created_at = null;

    /** @var string|null Дата последнего обновления */
    public ?string $updated_at = null;

    /**
     * Список ролей пользователя (заполняется через Репозиторий)
     * @var array
     */
    public array $roles = [];

    /**
     * Проверяет наличие роли в загруженном массиве ролей модели
     *
     * @param string $slug Слог роли (admin, user, etc)
     * @return bool
     */
    public function hasRole(string $slug): bool
    {
        return in_array($slug, array_column($this->roles, 'slug'));
    }
}
