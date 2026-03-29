<?php

namespace Repositorys;

use Models\UserModel;

/**
 * Репозиторий для работы с пользователями и их ролями
 */
class UserRepository extends BaseRepository
{
    /**
     * Находит пользователя по ID
     *
     * @param int $id
     * @return UserModel|null
     */
    public function find(int $id): ?UserModel
    {
        $data = $this->db->row("SELECT * FROM users WHERE id = :id", [
            'id' => $id
        ]);

        return $data ? UserModel::fromArray($data) : null;
    }

    /**
     * Получает все роли пользователя через связующую таблицу
     *
     * @param int $userId
     * @return array
     */
    public function getUserRoles(int $userId): array
    {
        return $this->db->all("
            SELECT roles.* 
            FROM roles
            JOIN user_role ON roles.id = user_role.role_id
            WHERE user_role.user_id = :userId
        ", ['userId' => $userId]);
    }

    /**
     * Проверяет наличие роли у пользователя по её слагу
     *
     * @param int $userId
     * @param string $roleSlug
     * @return bool
     */
    public function hasRole(int $userId, string $roleSlug): bool
    {
        $row = $this->db->row("
            SELECT user_role.user_id 
            FROM user_role
            JOIN roles ON user_role.role_id = roles.id
            WHERE user_role.user_id = :uid AND roles.slug = :slug
        ", ['uid' => $userId, 'slug' => $roleSlug]);

        return !empty($row);
    }

    /**
     * Назначает роль пользователю (игнорирует дубликаты)
     *
     * @param int $userId
     * @param int $roleId
     * @return bool
     */
    public function assignRole(int $userId, int $roleId): bool
    {
        // Простая проверка, чтобы не плодить ошибки уникальности
        $exists = $this->db->row(
            "SELECT user_id FROM user_role WHERE user_id = :uid AND role_id = :rid",
            ['uid' => $userId, 'rid' => $roleId]
        );

        if ($exists) {
            return true;
        }

        $stmt = $this->db->query(
            "INSERT INTO user_role (user_id, role_id) VALUES (:uid, :rid)",
            ['uid' => $userId, 'rid' => $roleId]
        );

        return $stmt->rowCount() > 0;
    }

    /**
     * Возвращает всех пользователей
     *
     * @return UserModel[]
     */
    public function all(): array
    {
        $rows = $this->db->all("SELECT * FROM users ORDER BY id DESC");
        return array_map(fn($row) => UserModel::fromArray($row), $rows);
    }

    /**
     * Создает нового пользователя с хешированием пароля
     *
     * @param array $data
     * @return int
     */
    public function store(array $data): int
    {
        $this->db->query(
            "INSERT INTO users (name, email, password, created_at) VALUES (:name, :email, :password, NOW())",
            [
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => password_hash($data['password'], PASSWORD_DEFAULT)
            ]
        );

        return (int)$this->db->lastInsertId();
    }
}
