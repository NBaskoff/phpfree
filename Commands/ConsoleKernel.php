<?php

namespace Commands;

use Core\Path;

/**
 * Ядро управления консольными командами
 */
class ConsoleKernel
{
    /**
     * Список зарегистрированных команд
     * @var array
     */
    protected array $commands = [];

    /**
     * Загружает команды из конфигурационного файла
     */
    public function __construct()
    {
        $configPath = Path::configs('commands.php');
        if (file_exists($configPath)) {
            $this->commands = require $configPath;
        }
    }

    /**
     * Обработка входящего консольного запроса
     *
     * @param array $argv Аргументы из терминала
     * @return void
     */
    public function handle(array $argv): void
    {
        // Название команды (например, make:migration)
        $commandName = $argv[1] ?? 'list';

        if (!isset($this->commands[$commandName])) {
            echo "\033[31mОшибка: Команда '{$commandName}' не найдена в config/commands.php\033[0m\n";
            return;
        }

        $commandClass = $this->commands[$commandName];

        // Создаем экземпляр команды напрямую
        if (class_exists($commandClass)) {
            $command = new $commandClass();

            // Передаем аргументы (начиная со второго индекса) в метод execute
            $command->execute(array_slice($argv, 2));
        } else {
            echo "\033[31mОшибка: Класс '{$commandClass}' не найден.\033[0m\n";
        }
    }
}
