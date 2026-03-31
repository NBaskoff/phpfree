<?php

namespace Commands;

use Core\Path;
use Contracts\DatabaseContract;
use Exception;

/**
 * Команда для запуска миграций с поддержкой батчей
 */
class MigrateCommand extends BaseCommand
{
    /**
     * PHP 8.4: Внедряем базу данных напрямую.
     * Resolver сам создаст её при вызове команды из ConsoleKernel.
     */
    public function __construct(
        private readonly DatabaseContract $db // Используем readonly свойство
    ) {}

    public function execute(array $args): void
    {
        $this->prepareMigrationsTable(); // Создаем таблицу если нет
        $executed = $this->getExecutedMigrations(); // Получаем список уже выполненных
        $currentBatch = $this->getNextBatchNumber(); // Определяем номер батча

        $dir = Path::migrations(); // Путь к папке миграций
        if (!is_dir($dir)) return; // Выход если папки нет

        $files = glob($dir . '/*.php'); // Берем все PHP файлы
        sort($files); // Сортируем по имени (по дате в имени)

        $count = 0; // Счетчик выполненных файлов
        foreach ($files as $file) {
            $fileName = basename($file); // Имя файла
            if (in_array($fileName, $executed)) continue; // Пропуск если уже была

            $this->runMigration($file, $fileName, $currentBatch); // Запуск миграции
            $count++; // Инкремент
        }

        $count > 0
            ? $this->success("Миграция завершена. Батч: $currentBatch, файлов: $count")
            : $this->info("Нет новых миграций."); // Итоговый вывод
    }

    private function prepareMigrationsTable(): void
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS migrations (
            id SERIAL PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            batch INTEGER NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )"); // Создание таблицы истории
    }

    private function getNextBatchNumber(): int
    {
        $row = $this->db->row("SELECT MAX(batch) as max_batch FROM migrations"); // Ищем макс. батч
        return ((int)($row['max_batch'] ?? 0)) + 1; // Увеличиваем на 1
    }

    private function getExecutedMigrations(): array
    {
        return array_column($this->db->all("SELECT migration FROM migrations"), 'migration'); // Список имен файлов
    }

    private function runMigration(string $file, string $fileName, int $batch): void
    {
        require_once $file; // Подключаем файл миграции
        $className = $this->getClassName($fileName); // Вычисляем имя класса
        $migration = new $className(); // Создаем объект миграции

        $this->db->beginTransaction(); // Открываем транзакцию
        try {
            $migration->up($this->db); // Запускаем метод up и передаем объект БД
            $this->db->query("INSERT INTO migrations (migration, batch) VALUES (:m, :b)", [
                'm' => $fileName,
                'b' => $batch
            ]); // Записываем в историю
            $this->db->commit(); // Применяем изменения
            $this->info("Выполнено: $fileName"); // Лог
        } catch (Exception $e) {
            $this->db->rollBack(); // Откат при ошибке
            $this->error("Ошибка в $fileName: " . $e->getMessage()); // Вывод ошибки
            exit; // Прекращаем выполнение
        }
    }

    private function getClassName(string $fileName): string
    {
        $parts = explode('_', str_replace('.php', '', $fileName)); // Разбиваем имя файла по подчеркиванию
        return implode('', array_map('ucfirst', array_slice($parts, 4))); // Собираем имя класса из конца
    }
}
