<?php

namespace Controllers;

use Actions\User\ListUsersAction;
use Exception;
use Requests\UserRequest;

class UserController extends BaseController
{
    /**
     * Вывод списка пользователей через вызываемый экшен
     * @throws Exception
     */
    public function actionIndexGet(UserRequest $request, ListUsersAction $action): string
    {
        // $request() отдает массив, $action() его принимает и возвращает данные для View
        return $this->display('user/index', $action($request()));
    }
}
