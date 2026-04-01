<?php

namespace Commands;

use Core\Path;

/**
 * Команда для генерации шаблона анонимной миграции
 */
class MakeMigrationCommand extends BaseCommand
{
    /**
     * Создает файл миграции в директории, указанной в Path::migrations()
     */
    public function execute(array $args): void
    {
        // Проверяем наличие имени миграции в аргументах
        if (empty($args[0])) {
            $this->error("Ошибка: Укажите имя миграции (например: create_users_table)");
            return;
        }

        $name = strtolower($args[0]); // Имя в нижнем регистре (snake_case)
        $timestamp = date('Y_m_d_His'); // Временная метка для сортировки
        $fileName = "{$timestamp}_{$name}.php"; // Имя файла: дата_имя.php

        $directory = Path::migrations(); // Получаем путь к папке миграций из конфига через Path

        // Создаем директорию, если она еще не существует
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $path = $directory . DIRECTORY_SEPARATOR . $fileName;

        // Записываем шаблон анонимного класса в файл
        if (file_put_contents($path, $this->getTemplate())) {
            $this->success("Миграция создана: {$path}");
        } else {
            $this->error("Ошибка: Не удалось записать файл миграции.");
        }
    }

    /**
     * Возвращает PHP код шаблона анонимной миграции (стандарт return new class)
     */
    private function getTemplate(): string
    {
        return <<<'PHP'
<?php

use Contracts\DatabaseContract;

/**
 * Анонимная миграция (PHP 8.4 ready)
 */
return new class 
{
    /**
     * Выполнение миграции: создание таблиц или изменение структуры
     */
    public function up(DatabaseContract $db): void
    {
        // $db->query("CREATE TABLE ...");
    }

    /**
     * Откат миграции: удаление таблиц или отмена изменений
     */
    public function down(DatabaseContract $db): void
    {
        // $db->query("DROP TABLE ...");
    }
};
PHP;
    }
}
