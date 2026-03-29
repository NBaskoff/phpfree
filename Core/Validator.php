<?php

namespace Core;

/**
 * Класс для фильтрации и валидации входных данных
 */
class Validator
{
    /**
     * Очищает массив данных от лишних пробелов и HTML-тегов
     *
     * @param array $data
     * @return array
     */
    public static function filter(array $data): array
    {
        return array_map(function ($value) {
            if (is_string($value)) {
                return trim(strip_tags($value));
            }
            return $value;
        }, $data);
    }

    /**
     * Проверяет корректность формата Email
     *
     * @param string $email
     * @return bool
     */
    public static function email(string $email): bool
    {
        return (bool)filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}
