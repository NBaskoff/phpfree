<?php

namespace Commands;

use Contracts\DatabaseContract;

// Контракт
use Core\Path;

// Пути
use Exception;

// Исключения

/**
 * Команда отката последней ПАРТИИ (батча) миграций
 */
class MigrateRollbackCommand extends BaseCommand
{
    /**
     * PHP 8.4: Внедряем базу данных через конструктор.
     * Resolver сам создаст DatabaseContract и подставит сюда.
     */
    public function __construct(
        protected readonly DatabaseContract $db // База данных
    )
    {
    }

    public function execute(array $args): void
    {
        $res = $this->db->row("SELECT MAX(batch) as last_batch FROM migrations"); // Ищем макс. батч
        $lastBatch = $res['last_batch'] ?? null; // Номер батча

        if (!$lastBatch) {
            $this->warn("Нет миграций для отката."); // Предупреждение
            return; // Выход
        }

        $toRollback = $this->db->all(
            "SELECT * FROM migrations WHERE batch = :b ORDER BY id DESC",
            ['b' => $lastBatch]
        ); // Выбираем миграции батча

        $this->info("Откат батча №{$lastBatch} (" . count($toRollback) . " миграций)..."); // Лог

        foreach ($toRollback as $row) {
            $this->rollbackFile($row['migration'], (int)$row['id']); // Откат каждого файла
        }

        $this->success("Откат батча №{$lastBatch} завершен."); // Успех
    }

    private function rollbackFile(string $fileName, int $id): void
    {
        $file = Path::root('Migrations' . DIRECTORY_SEPARATOR . $fileName); // Полный путь
        if (!file_exists($file)) {
            $this->error("Файл $fileName не найден."); // Ошибка файла
            return; // Пропуск
        }

        require_once $file; // Подключение файла миграции
        $className = $this->getClassName($fileName); // Имя класса
        $migration = new $className(); // Создание объекта миграции

        $this->db->beginTransaction(); // Транзакция
        try {
            $migration->down($this->db); // Вызов метода down
            $this->db->query("DELETE FROM migrations WHERE id = :id", ['id' => $id]); // Удаление из истории
            $this->db->commit(); // Применение
            $this->info("Откачено: $fileName"); // Лог
        } catch (Exception $e) {
            $this->db->rollBack(); // Откат при ошибке
            $this->error("Ошибка при откате $fileName: " . $e->getMessage()); // Вывод ошибки
            exit; // Остановка
        }
    }

    private function getClassName(string $fileName): string
    {
        $parts = explode('_', str_replace('.php', '', $fileName)); // Парсинг имени
        return implode('', array_map('ucfirst', array_slice($parts, 4))); // Имя класса
    }
}
