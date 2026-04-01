<?php

namespace Commands;

use Contracts\DatabaseContract;
use Core\Path;
use Exception;

/**
 * Команда отката последней ПАРТИИ (батча) анонимных миграций
 */
class MigrateRollbackCommand extends BaseCommand
{
    /**
     * PHP 8.4: Внедряем базу данных через Constructor Property Promotion.
     */
    public function __construct(
        protected readonly DatabaseContract $db
    ) {}

    /**
     * Основной метод выполнения отката
     */
    public function execute(array $args): void
    {
        // Ищем номер последнего батча в таблице истории
        $res = $this->db->row("SELECT MAX(batch) as last_batch FROM migrations");
        $lastBatch = $res['last_batch'] ?? null;

        if (!$lastBatch) {
            $this->warn("Нет выполненных миграций для отката.");
            return;
        }

        // Выбираем все миграции, принадлежащие последнему батчу, в обратном порядке
        $toRollback = $this->db->all(
            "SELECT * FROM migrations WHERE batch = :b ORDER BY id DESC",
            ['b' => $lastBatch]
        );

        $this->info("Откат батча №{$lastBatch} (файлов: " . count($toRollback) . ")...");

        foreach ($toRollback as $row) {
            $this->rollbackFile($row['migration'], (int)$row['id']);
        }

        $this->success("Откат батча №{$lastBatch} успешно завершен.");
    }

    /**
     * Выполняет метод down() для конкретного файла миграции
     */
    private function rollbackFile(string $fileName, int $id): void
    {
        $file = Path::migrations($fileName);

        if (!file_exists($file)) {
            $this->error("Файл миграции {$fileName} не найден на диске. Удаление записи из БД...");
            $this->db->query("DELETE FROM migrations WHERE id = :id", ['id' => $id]);
            return;
        }

        // Получаем объект анонимного класса через include (стандарт return new class)
        $migration = include $file;

        if (!is_object($migration)) {
            $this->error("Ошибка: Файл {$fileName} должен возвращать объект 'return new class'.");
            return;
        }

        $this->db->beginTransaction(); // Стартуем транзакцию для безопасности

        try {
            // Вызываем метод отката, передавая объект БД
            $migration->down($this->db);

            // Удаляем запись об этой миграции из таблицы истории
            $this->db->query("DELETE FROM migrations WHERE id = :id", ['id' => $id]);

            $this->db->commit(); // Применяем изменения
            $this->info("Откачено: $fileName");

        } catch (Exception $e) {
            $this->db->rollBack(); // Откатываем изменения при ошибке в SQL
            $this->error("Ошибка при откате {$fileName}: " . $e->getMessage());
            exit; // Прекращаем выполнение всей команды
        }
    }
}
