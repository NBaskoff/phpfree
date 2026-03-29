<?php

namespace Repositorys;

use Models\UserModel;

/**
 * Репозиторий для работы с таблицей пользователей
 */
class UserRepository extends BaseRepository
{
    /**
     * Находит пользователя по его первичному ключу
     *
     * @param int $id Идентификатор пользователя
     * @return UserModel|null Объект модели или null, если запись не найдена
     */
    public function find(int $id): ?UserModel
    {
        $data = $this->db->row("SELECT * FROM users WHERE id = :id", [
            'id' => $id
        ]);

        return $data ? UserModel::fromArray($data) : null;
    }

    /**
     * Находит пользователя по адресу электронной почты
     *
     * @param string $email
     * @return UserModel|null
     */
    public function findByEmail(string $email): ?UserModel
    {
        $data = $this->db->row("SELECT * FROM users WHERE email = :email", [
            'email' => $email
        ]);

        return $data ? UserModel::fromArray($data) : null;
    }

    /**
     * Возвращает список всех пользователей
     *
     * @return UserModel[] Массив объектов UserModel
     */
    public function all(): array
    {
        $rows = $this->db->all("SELECT * FROM users ORDER BY id DESC");

        return array_map(fn($row) => UserModel::fromArray($row), $rows);
    }

    /**
     * Сохраняет данные нового пользователя в базу
     *
     * @param array $data Данные пользователя (name, email)
     * @return int ID созданной записи
     */
    public function store(array $data): int
    {
        $this->db->query(
            "INSERT INTO users (name, email, created_at) VALUES (:name, :email, NOW())",
            [
                'name'  => $data['name'],
                'email' => $data['email']
            ]
        );

        return (int)$this->db->lastInsertId();
    }

    /**
     * Обновляет данные существующего пользователя
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->query(
            "UPDATE users SET name = :name, email = :email, updated_at = NOW() WHERE id = :id",
            [
                'id'    => $id,
                'name'  => $data['name'],
                'email' => $data['email']
            ]
        );

        return $stmt->rowCount() > 0;
    }

    /**
     * Удаляет пользователя из базы данных
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->query("DELETE FROM users WHERE id = :id", ['id' => $id]);

        return $stmt->rowCount() > 0;
    }
}
