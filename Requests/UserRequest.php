<?php

namespace Requests;

use Core\Request;

class UserRequest extends Request
{
    protected function validate(): void
    {
        // Правила валидации для GET /users (например, поиск или пагинация)
        if ($this->get('page') && !is_numeric($this->get('page'))) {
            $this->addError("Page must be a number");
        }
    }
}
