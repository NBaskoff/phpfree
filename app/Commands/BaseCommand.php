<?php

namespace Commands;

/**
 * Абстрактный класс для всех консольных команд
 */
abstract class BaseCommand
{
    /**
     * @param array $args Аргументы команды
     */
    abstract public function execute(array $args): void;

    /** Вывод успешного сообщения (зеленый) */
    protected function success(string $msg): void
    {
        echo "\033[32m[OK] {$msg}\033[0m\n";
    }

    /** Вывод ошибки (красный) */
    protected function error(string $msg): void
    {
        echo "\033[31m[ERROR] {$msg}\033[0m\n";
    }

    /** Вывод предупреждения (желтый) */
    protected function warn(string $msg): void
    {
        echo "\033[33m[WARN] {$msg}\033[0m\n";
    }

    /** Обычный информационный вывод */
    protected function info(string $msg): void
    {
        echo "{$msg}\n";
    }
}
