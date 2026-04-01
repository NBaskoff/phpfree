<?php

use Contracts\DatabaseContract;

return new class
{
    public function up(DatabaseContract $db): void
    {
        $db->query("
            CREATE TABLE IF NOT EXISTS roles (
                id SERIAL PRIMARY KEY,
                name VARCHAR(50) NOT NULL UNIQUE,
                slug VARCHAR(50) NOT NULL UNIQUE,
                description VARCHAR(255) NULL
            )
        ");

        // Сразу добавим базовые роли
        $db->query("INSERT INTO roles (name, slug) VALUES ('Администратор', 'admin'), ('Пользователь', 'user')");
    }

    public function down(DatabaseContract $db): void
    {
        $db->query("DROP TABLE IF EXISTS roles");
    }
};