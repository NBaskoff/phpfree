<?php

namespace Commands;

use Core\Resolver; // Резолвер
use Contracts\DatabaseContract; // Контракт БД

class MigrateRefreshCommand extends BaseCommand
{
    public function __construct(
        protected readonly DatabaseContract $db // Внедрение БД через DI
    ) {}

    public function execute(array $args): void
    {
        $this->warn("Начинается полный сброс базы данных..."); // Предупреждение

        $resolver = new Resolver(); // Создаем резолвер без скобок (PHP 8.4)

        while (true) { // Цикл полной очистки
            $last = $this->db->row("SELECT id FROM migrations LIMIT 1"); // Проверка наличия записей
            if (!$last) break; // Выход, если пусто

            // Получаем команду отката через Resolver (автоматически прокинет БД в конструктор)
            $rollback = $resolver->resolveDependency(MigrateRollbackCommand::class);
            $rollback->execute([]); // Выполняем откат батча
        }

        $this->info("База данных очищена. Запуск миграций..."); // Инфо

        // Получаем команду миграции через Resolver (внедрит БД автоматически)
        $migrate = $resolver->resolveDependency(MigrateCommand::class);
        $migrate->execute([]); // Запускаем создание таблиц

        $this->success("Команда migrate:refresh успешно выполнена."); // Успех
    }
}
