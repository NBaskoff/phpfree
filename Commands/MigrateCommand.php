<?php

namespace Commands;

use Core\Path;
use Core\Contract;
use Contracts\DatabaseContract;
use Exception;

/**
 * Команда для запуска миграций с поддержкой батчей
 */
class MigrateCommand extends BaseCommand
{
    private DatabaseContract $db;

    public function execute(array $args): void
    {
        $this->db = Contract::make(DatabaseContract::class);
        $this->prepareMigrationsTable();

        $executed = $this->getExecutedMigrations();

        // Определяем номер текущего батча (максимальный + 1)
        $currentBatch = $this->getNextBatchNumber();

        $dir = Path::root('Migrations');
        if (!is_dir($dir)) return;

        $files = glob($dir . '/*.php');
        sort($files);

        $count = 0;
        foreach ($files as $file) {
            $fileName = basename($file);
            if (in_array($fileName, $executed)) continue;

            $this->runMigration($file, $fileName, $currentBatch);
            $count++;
        }

        $count > 0 ? $this->success("Миграция завершена. Батч: $currentBatch, файлов: $count") : $this->info("Нет новых миграций.");
    }

    /**
     * Подготовка таблицы с колонкой batch
     */
    private function prepareMigrationsTable(): void
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS migrations (
            id SERIAL PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            batch INTEGER NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
    }

    private function getNextBatchNumber(): int
    {
        $row = $this->db->row("SELECT MAX(batch) as max_batch FROM migrations");
        return ($row['max_batch'] ?? 0) + 1;
    }

    private function getExecutedMigrations(): array
    {
        return array_column($this->db->all("SELECT migration FROM migrations"), 'migration');
    }

    private function runMigration(string $file, string $fileName, int $batch): void
    {
        require_once $file;
        $className = $this->getClassName($fileName);
        $migration = new $className();

        $this->db->beginTransaction();
        try {
            $migration->up($this->db);
            $this->db->query("INSERT INTO migrations (migration, batch) VALUES (:m, :b)", [
                'm' => $fileName,
                'b' => $batch
            ]);
            $this->db->commit();
            $this->info("Выполнено: $fileName");
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->error("Ошибка в $fileName: " . $e->getMessage());
            exit;
        }
    }

    private function getClassName(string $fileName): string
    {
        $parts = explode('_', str_replace('.php', '', $fileName));
        return implode('', array_map('ucfirst', array_slice($parts, 4)));
    }
}
