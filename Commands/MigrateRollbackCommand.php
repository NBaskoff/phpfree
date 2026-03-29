<?php

namespace Commands;

use Core\Contract;
use Contracts\DatabaseContract;
use Core\Path;
use Exception;

/**
 * Команда отката последней ПАРТИИ (батча) миграций
 */
class MigrateRollbackCommand extends BaseCommand
{
    private DatabaseContract $db;

    public function execute(array $args): void
    {
        $this->db = Contract::make(DatabaseContract::class);

        // Находим номер последнего батча
        $res = $this->db->row("SELECT MAX(batch) as last_batch FROM migrations");
        $lastBatch = $res['last_batch'] ?? null;

        if (!$lastBatch) {
            $this->warn("Нет миграций для отката.");
            return;
        }

        // Получаем все миграции этого батча в обратном порядке
        $toRollback = $this->db->all(
            "SELECT * FROM migrations WHERE batch = :b ORDER BY id DESC",
            ['b' => $lastBatch]
        );

        $this->info("Откат батча №{$lastBatch} (" . count($toRollback) . " миграций)...");

        foreach ($toRollback as $row) {
            $this->rollbackFile($row['migration'], (int)$row['id']);
        }

        $this->success("Откат батча №{$lastBatch} завершен.");
    }

    private function rollbackFile(string $fileName, int $id): void
    {
        $file = Path::root('Migrations' . DIRECTORY_SEPARATOR . $fileName);
        if (!file_exists($file)) {
            $this->error("Файл $fileName не найден.");
            return;
        }

        require_once $file;
        $className = $this->getClassName($fileName);
        $migration = new $className();

        $this->db->beginTransaction();
        try {
            $migration->down($this->db);
            $this->db->query("DELETE FROM migrations WHERE id = :id", ['id' => $id]);
            $this->db->commit();
            $this->info("Откачено: $fileName");
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->error("Ошибка при откате $fileName: " . $e->getMessage());
            exit;
        }
    }

    private function getClassName(string $fileName): string
    {
        $parts = explode('_', str_replace('.php', '', $fileName));
        return implode('', array_map('ucfirst', array_slice($parts, 4)));
    }
}
