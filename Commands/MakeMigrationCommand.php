<?php

namespace Commands;

use Core\Path;

/**
 * Команда для создания нового файла миграции в папке /Migrations
 */
class MakeMigrationCommand extends BaseCommand
{
    /**
     * Выполнение создания файла-шаблона миграции
     *
     * @param array $args Аргументы командной строки
     * @return void
     */
    public function execute(array $args): void
    {
        if (empty($args[0])) {
            $this->error("Ошибка: Укажите имя миграции (например: create_users_table)");
            return;
        }

        // Подготовка имен
        $name = strtolower($args[0]);
        $timestamp = date('Y_m_d_His');
        $className = $this->convertToClassName($name);
        $fileName = "{$timestamp}_{$name}.php";

        // Путь к папке /Migrations в корне
        $directory = Path::root('Migrations');

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $path = $directory . DIRECTORY_SEPARATOR . $fileName;

        // Запись шаблона в файл
        file_put_contents($path, $this->getTemplate($className));

        $this->success("Миграция создана: Migrations/{$fileName}");
    }

    /**
     * Преобразует snake_case в CamelCase для имени класса миграции
     *
     * @param string $name
     * @return string
     */
    private function convertToClassName(string $name): string
    {
        return str_replace('_', '', ucwords($name, '_'));
    }

    /**
     * Возвращает заготовку PHP-кода для новой миграции
     *
     * @param string $className
     * @return string
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
     * Выполнение миграции (создание таблиц/колонок)
     * 
     * @param DatabaseContract \$db
     * @return void
     */
    public function up(DatabaseContract \$db): void
    {
        // \$db->query("CREATE TABLE ...");
    }

    /**
     * Откат миграции (удаление таблиц/колонок)
     * 
     * @param DatabaseContract \$db
     * @return void
     */
    public function down(DatabaseContract \$db): void
    {
        // \$db->query("DROP TABLE ...");
    }
}
PHP;
    }
}
