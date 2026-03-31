<?php

namespace Requests\Validations;

use Contracts\RuleContract;

class EmailRequestValidation implements RuleContract
{
    public function __invoke(mixed $value): bool
    {
        return (bool)filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    public function getMessage(string $field): string
    {
        return "Поле {$field} должно быть корректным email адресом.";
    }
}
