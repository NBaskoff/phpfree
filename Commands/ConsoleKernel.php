<?php

namespace Commands;

use Core\Path;
use Core\Resolver;

// Подключаем резолвер

class ConsoleKernel
{
    protected array $commands = []; // Массив из config/commands.php

    public function __construct()
    {
        $configPath = Path::configs('commands.php'); // Путь к конфигу
        if (file_exists($configPath)) $this->commands = require $configPath; // Загрузка команд
    }

    public function handle(array $argv): void
    {
        $commandName = $argv[1] ?? 'list'; // Берем имя команды

        if (!isset($this->commands[$commandName])) {
            echo "\033[31mОшибка: Команда '{$commandName}' не найдена.\033[0m\n"; // Ошибка
            return;
        }

        $commandClass = $this->commands[$commandName]; // Класс из конфига

        if (class_exists($commandClass)) {
            $command = new Resolver()->resolveDependency($commandClass); // Создаем команду через DI
            $command->execute(array_slice($argv, 2)); // Запуск выполнения
        } else {
            echo "\033[31mОшибка: Класс '{$commandClass}' не найден.\033[0m\n"; // Ошибка класса
        }
    }
}
