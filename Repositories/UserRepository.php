<?php

namespace Repositories;

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
     * Находит пользователя и сразу подгружает его роли в объект модели
     *
     * @param int $id
     * @return UserModel|null
     */
    public function findWithRoles(int $id): ?UserModel
    {
        $user = $this->find($id);

        if ($user) {
            $user->roles = $this->getUserRoles($id);
        }

        return $user;
    }

    /**
     * Получает все роли пользователя из базы данных
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
     * Прямая проверка наличия роли в базе данных (без загрузки модели)
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
     * Назначает роль пользователю
     *
     * @param int $userId
     * @param int $roleId
     * @return bool
     */
    public function assignRole(int $userId, int $roleId): bool
    {
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
     * Создает нового пользователя
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

    /**
     * Обогащает модели пользователей их ролями через передачу по ссылке
     *
     * @param \Models\UserModel[] &$users Массив объектов UserModel, передаваемый по ссылке
     */
    public function getUsersRoles(array &$users): void // Изменяем оригинал массива объектов
    {
        if (empty($users)) return; // Выходим, если список пуст

        $userIds = array_map(fn($u) => $u->id, $users); // Извлекаем ID из моделей (PHP 8.4)
        $placeholders = implode(',', array_fill(0, count($userIds), '?')); // Готовим плейсхолдеры ?,?,?

        // Один запрос для получения всех ролей по списку ID пользователей
        $rows = $this->db->all("
        SELECT roles.*, user_role.user_id 
        FROM roles
        JOIN user_role ON roles.id = user_role.role_id
        WHERE user_role.user_id IN ($placeholders)
    ", $userIds);

        $rolesMap = []; // Группируем роли по пользователям
        foreach ($rows as $row) {
            $uid = $row['user_id']; // ID владельца роли
            unset($row['user_id']); // Удаляем техническое поле user_id
            $rolesMap[$uid][] = $row; // Складываем роль в карту соответствия
        }

        foreach ($users as $user) {
            // Записываем массив ролей в свойство объекта UserModel
            $user->roles = $rolesMap[$user->id] ?? []; // Если ролей нет — пустой массив
        }
    }


}
