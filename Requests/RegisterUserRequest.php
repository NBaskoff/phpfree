<?php

namespace Requests;

use Core\Contract;
use Contracts\DatabaseContract;
use Requests\Validations\RequiredRequestValidation;
use Requests\Validations\EmailRequestValidation;
use Requests\Validations\UniqueRequestValidation;

class RegisterUserRequest extends BaseRequest
{
    protected function validate(): void
    {
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
