<?php

namespace ViewHelpers;

/**
 * Хелпер для форматирования дат в шаблонах
 */
class DateViewHelper
{
    /**
     * Форматирует строку даты из БД в человекопонятный вид
     *
     * @param string|null $date Строка даты из базы (Y-m-d H:i:s)
     * @param string $format Желаемый формат вывода
     * @return string
     */
    public function __invoke(?string $date, string $format = 'd.m.Y H:i'): string
    {
        if (empty($date)) {
            return 'Не указана';
        }

        $timestamp = strtotime($date);

        return $timestamp ? date($format, $timestamp) : 'Ошибка даты';
    }
}
