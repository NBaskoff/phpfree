<?php

namespace Commands;

use Core\Path;
use Exception;

/**
 * Команда для генерации шаблона миграции
 */
class MakeMigrationCommand extends BaseCommand
{
    /**
     * Создает файл миграции в директории /Migrations
     */
    public function execute(array $args): void
    {
        if (empty($args[0])) { // Проверяем наличие имени
            $this->error("Ошибка: Укажите имя миграции (например: create_users_table)"); // Вывод ошибки
            return; // Выход
        }

        $name = strtolower($args[0]); // Приводим к нижнему регистру
        $timestamp = date('Y_m_d_His'); // Генерируем метку времени
        $className = $this->convertToClassName($name); // Преобразуем в CamelCase
        $fileName = "{$timestamp}_{$name}.php"; // Формируем имя файла

        $directory = Path::root('Migrations'); // Определяем путь к папке миграций
        if (!is_dir($directory)) mkdir($directory, 0755, true); // Создаем папку если нет

        $path = $directory . DIRECTORY_SEPARATOR . $fileName; // Полный путь к файлу
        file_put_contents($path, $this->getTemplate($className)); // Записываем шаблон

        $this->success("Миграция создана: Migrations/{$fileName}"); // Успешное завершение
    }

    /**
     * Преобразует snake_case в CamelCase для соответствия классу
     */
    private function convertToClassName(string $name): string
    {
        return str_replace('_', '', ucwords($name, '_')); // Формирование имени класса
    }

    /**
     * Возвращает PHP код шаблона
     */
    private function getTemplate(string $className): string
    {
        return <<<PHP
<?php

use Contracts\DatabaseContract;

/**
 * Класс миграции {$className}
 */
class {$className}
{
    /**
     * Выполнение миграции
     */
    public function up(DatabaseContract \$db): void
    {
        // \$db->query("CREATE TABLE ...");
    }

    /**
     * Откат миграции
     */
    public function down(DatabaseContract \$db): void
    {
        // \$db->query("DROP TABLE ...");
    }
}
PHP;
    }
}
