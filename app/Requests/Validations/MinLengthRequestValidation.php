<?php

namespace Requests\Validations;

use Contracts\RuleContract;

class MinLengthRequestValidation implements RuleContract
{
    public function __construct(private int $min) {}

    public function __invoke(mixed $value): bool
    {
        return mb_strlen((string)$value) >= $this->min;
    }

    public function getMessage(string $field): string
    {
        return "Поле {$field} должно быть не короче {$this->min} символов.";
    }
}
