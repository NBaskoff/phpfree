<?php

namespace Requests;

use Contracts\DatabaseContract;
use Core\Contract;
use Requests\Validations\RequiredRequestValidation;
use Requests\Validations\EmailRequestValidation;
use Requests\Validations\MinLengthRequestValidation;
use Requests\Validations\UniqueRequestValidation;

/**
 * Валидация данных регистрации
 */
class RegisterUserRequest extends BaseRequest
{
    protected function validate(): void
    {
        $this->validateField('name', [
            new RequiredRequestValidation(),
            new MinLengthRequestValidation(2)
        ]);

        // Получаем БД только для этого правила
        $db = Contract::make(DatabaseContract::class);

        $this->validateField('email', [
            new RequiredRequestValidation(),
            new EmailRequestValidation(),
            // Проверяем, что email уникален в таблице users
            new UniqueRequestValidation($db, 'users', 'email')
        ]);

        $this->validateField('password', [
            new RequiredRequestValidation()
        ]);
    }
}