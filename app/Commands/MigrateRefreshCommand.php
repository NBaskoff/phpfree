<?php

namespace Commands;

use Core\Resolver;
use Contracts\DatabaseContract;

/**
 * Команда для полного перезапуска базы данных (откат всех миграций и повторный накат)
 */
class MigrateRefreshCommand extends BaseCommand
{
    /**
     * PHP 8.4: Внедряем базу данных через конструктор.
     */
    public function __construct(
        protected readonly DatabaseContract $db
    ) {}

    /**
     * Выполнение команды очистки и наката
     */
    public function execute(array $args): void
    {
        $this->warn("Внимание: Начинается полный сброс базы данных...");

        // Проверяем существование таблицы миграций перед началом
        $tableExists = $this->db->row("SHOW TABLES LIKE 'migrations'");

        if ($tableExists) {
            // Цикл отката: пока в таблице есть записи, вызываем команду Rollback
            while (true) {
                $last = $this->db->row("SELECT id FROM migrations LIMIT 1");
                if (!$last) {
                    break; // Все батчи откачены
                }

                // PHP 8.4: Создаем Resolver и получаем команду отката со всеми зависимостями
                $rollback = new Resolver()->resolveDependency(MigrateRollbackCommand::class);
                $rollback->execute([]);
            }
        }

        $this->info("База данных очищена. Запуск свежих миграций...");

        // Получаем команду миграции через Resolver (он сам прокинет DatabaseContract в конструктор)
        $migrate = new Resolver()->resolveDependency(MigrateCommand::class);
        $migrate->execute([]);

        $this->success("Команда migrate:refresh успешно выполнена. База приведена в исходное состояние.");
    }
}
