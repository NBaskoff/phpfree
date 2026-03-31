<?php

namespace Requests\Validations;

use Contracts\RuleContract;
use Contracts\SessionContract;
use Core\Contract;

/**
 * Правило проверки CSRF-токена через сессию
 */
class CsrfRequestValidation implements RuleContract
{
    /**
     * @param mixed $value
     * @return bool
     */
    public function __invoke(mixed $value): bool
    {
        $session = Contract::make(SessionContract::class);
        $sessionToken = $session->get('_csrf');

        if (!$sessionToken || !is_string($value)) {
            return false;
        }

        return hash_equals((string)$sessionToken, (string)$value);
    }

    /**
     * @param string $field
     * @return string
     */
    public function getMessage(string $field): string
    {
        return "Ошибка безопасности: неверный или отсутствующий CSRF-токен.";
    }
}
