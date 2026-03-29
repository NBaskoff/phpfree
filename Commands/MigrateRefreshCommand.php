<?php

namespace Commands;

use Core\Contract;
use Contracts\DatabaseContract;

/**
 * Команда для полного пересоздания базы данных (Rollback всех + Migrate)
 */
class MigrateRefreshCommand extends BaseCommand
{
    /**
     * Выполнение полного сброса и повторного запуска миграций
     *
     * @param array $args
     * @return void
     */
    public function execute(array $args): void
    {
        /** @var DatabaseContract $db */
        $db = Contract::make(DatabaseContract::class);

        $this->warn("Начинается полный сброс базы данных...");

        // 1. Полный откат всех миграций по одной
        while (true) {
            $last = $db->row("SELECT id FROM migrations LIMIT 1");
            if (!$last) {
                break;
            }

            // Используем уже готовую логику отката (по батчам)
            $rollback = new MigrateRollbackCommand();
            $rollback->execute([]);
        }

        $this->info("База данных очищена. Запуск миграций...");

        // 2. Повторный запуск всех миграций
        $migrate = new MigrateCommand();
        $migrate->execute([]);

        $this->success("Команда migrate:refresh успешно выполнена.");
    }
}
