<?php

namespace Contracts;

/**
 * Контракт для правил валидации, вызываемых через __invoke
 */
interface RuleContract
{
    /**
     * Выполнение проверки значения
     *
     * @param mixed $value
     * @return bool
     */
    public function __invoke(mixed $value): bool;

    /**
     * Получение текста ошибки
     *
     * @param string $field
     * @return string
     */
    public function getMessage(string $field): string;
}
