<?php

namespace Requests;

use Requests\Validations\RequiredRequestValidation;
use Requests\Validations\EmailRequestValidation;
use Requests\Validations\MinLengthRequestValidation;

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

        $this->validateField('email', [
            new RequiredRequestValidation(),
            new EmailRequestValidation()
        ]);

        $this->validateField('password', [
            new RequiredRequestValidation(),
            new MinLengthRequestValidation(6)
        ]);
    }
}
