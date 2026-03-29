<?php

use Contracts\DatabaseContract;

/**
 * Миграция для создания таблицы пользователей
 */
class CreateUserTable
{
    /**
     * Запуск миграции: создание таблицы
     *
     * @param DatabaseContract $db
     * @return void
     */
    public function up(DatabaseContract $db): void
    {
        $db->query("
        CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(150) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            status VARCHAR(20) DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_users_email (email)
        )
    ");
    }

    /**
     * Откат миграции: удаление таблицы
     *
     * @param DatabaseContract $db
     * @return void
     */
    public function down(DatabaseContract $db): void
    {
        $db->query("DROP TABLE IF EXISTS users");
    }
}
