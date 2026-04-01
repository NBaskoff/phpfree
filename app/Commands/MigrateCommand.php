<?php

namespace Commands;

use Core\Path;
use Contracts\DatabaseContract;
use Exception;

/**
 * Команда для запуска анонимных миграций с поддержкой батчей
 */
class MigrateCommand extends BaseCommand
{
    /**
     * PHP 8.4: Внедряем базу данных через Constructor Property Promotion.
     */
    public function __construct(
        private readonly DatabaseContract $db
    ) {}

    /**
     * Основной метод выполнения команды
     */
    public function execute(array $args): void
    {
        $this->prepareMigrationsTable(); // Создаем таблицу истории, если её нет
        $executed = $this->getExecutedMigrations(); // Получаем список уже выполненных файлов

        $dir = Path::migrations(); // Путь к директории миграций из конфига
        if (!is_dir($dir)) {
            $this->error("Директория миграций не найдена: {$dir}");
            return;
        }

        $files = glob($dir . '/*.php'); // Собираем все PHP файлы миграций
        sort($files); // Сортируем по имени (временная метка в начале имени файла)

        $toExecute = [];
        foreach ($files as $file) {
            $fileName = basename($file);
            if (!in_array($fileName, $executed)) {
                $toExecute[] = ['file' => $file, 'name' => $fileName];
            }
        }

        if (empty($toExecute)) {
            $this->info("Нет новых миграций для запуска.");
            return;
        }

        $currentBatch = $this->getNextBatchNumber(); // Определяем номер текущей пачки (батча)

        foreach ($toExecute as $item) {
            $this->runMigration($item['file'], $item['name'], $currentBatch);
        }

        $this->success("Миграция завершена. Выполнено файлов: " . count($toExecute) . " (Батч: $currentBatch)");
    }

    /**
     * Создает служебную таблицу для хранения истории миграций
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

    /**
     * Возвращает номер следующего батча
     */
    private function getNextBatchNumber(): int
    {
        $row = $this->db->row("SELECT MAX(batch) as max_batch FROM migrations");
        return ((int)($row['max_batch'] ?? 0)) + 1;
    }

    /**
     * Возвращает список имен файлов уже выполненных миграций
     */
    private function getExecutedMigrations(): array
    {
        $result = $this->db->all("SELECT migration FROM migrations");
        return array_column($result, 'migration');
    }

    /**
     * Подключает файл миграции и выполняет метод up()
     */
    private function runMigration(string $file, string $fileName, int $batch): void
    {
        // Получаем объект анонимного класса, который возвращает файл миграции
        $migration = include $file;

        if (!is_object($migration)) {
            $this->error("Ошибка: Файл {$fileName} должен возвращать 'return new class'.");
            exit;
        }

        $this->db->beginTransaction(); // Стартуем транзакцию для безопасности данных

        try {
            // Запускаем миграцию, передавая объект БД
            $migration->up($this->db);

            // Фиксируем выполнение в таблице истории
            $this->db->query("INSERT INTO migrations (migration, batch) VALUES (:m, :b)", [
                'm' => $fileName,
                'b' => $batch
            ]);

            $this->db->commit(); // Применяем изменения в БД
            $this->info("Выполнено: $fileName");

        } catch (Exception $e) {
            $this->db->rollBack(); // Откатываем транзакцию при любой ошибке
            $this->error("Ошибка в миграции {$fileName}: " . $e->getMessage());
            exit; // Прекращаем выполнение всей команды
        }
    }
}
