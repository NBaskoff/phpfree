<?php

namespace Requests\Validations;

use Contracts\RuleContract;

class RequiredRequestValidation implements RuleContract
{
    public function __invoke(mixed $value): bool
    {
        return !empty($value) || $value === '0' || $value === 0;
    }

    public function getMessage(string $field): string
    {
        return "Поле {$field} обязательно для заполнения.";
    }
}
