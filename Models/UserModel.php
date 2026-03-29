<?php

namespace Models;

/**
 * Модель данных пользователя
 */
class UserModel extends BaseModel
{
    /**
     * @param int $id
     * @param string $name
     * @param string $email
     * @param string|null $created_at
     * @param string|null $updated_at
     * @param array $roles
     */
    public function __construct(
        public int $id = 0,
        public string $name = '',
        public string $email = '',
        public ?string $created_at = null,
        public ?string $updated_at = null,
        public array $roles = []
    ) {}

    /**
     * Проверяет наличие роли
     *
     * @param string $slug
     * @return bool
     */
    public function hasRole(string $slug): bool
    {
        return in_array($slug, array_column($this->roles, 'slug'));
    }
}
