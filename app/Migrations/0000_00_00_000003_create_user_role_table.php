<?php

use Contracts\DatabaseContract;

return new class
{
    public function up(DatabaseContract $db): void
    {
        // Название таблицы в единственном числе: user_role
        $db->query("
            CREATE TABLE IF NOT EXISTS user_role (
                user_id BIGINT UNSIGNED NOT NULL,
                role_id BIGINT UNSIGNED NOT NULL,
                PRIMARY KEY (user_id, role_id),
                CONSTRAINT fk_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                CONSTRAINT fk_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
            )
        ");
    }

    public function down(DatabaseContract $db): void
    {
        $db->query("DROP TABLE IF EXISTS user_role");
    }
};
