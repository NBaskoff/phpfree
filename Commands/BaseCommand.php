<?php

namespace Commands;

/**
 * Абстрактный класс для всех консольных команд
 */
abstract class BaseCommand
{
    /**
     * Выполнение логики команды
     *
     * @param array $args Параметры из консоли
     */
    abstract public function execute(array $args): void;

    /** Вывод текста в консоль */
    protected function info(string $message): void { echo "\033[32m{$message}\033[0m\n"; }
    protected function error(string $message): void { echo "\033[31m{$message}\033[0m\n"; }
}
