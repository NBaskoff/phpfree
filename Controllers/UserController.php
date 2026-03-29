<?php

namespace Controllers;

use Actions\User\ListUsersAction;
use Exception;

class UserController extends BaseController
{
    /**
     * Вывод списка пользователей через вызываемый экшен
     * @throws Exception
     */
    public function actionIndexGet(ListUsersAction $action): string
    {
        return $this->display('user/index', $action());
    }
}
