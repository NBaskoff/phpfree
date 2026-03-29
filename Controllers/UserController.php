<?php

namespace Controllers;

use Actions\User\ListUsersAction;

class UserController extends BaseController
{
    /**
     * Экшен внедряется автоматически через аргументы метода
     * @throws \Exception
     */
    public function actionIndexGet(ListUsersAction $action): string
    {
        $users = $action->execute();

        return $this->display('user/index', [
            'users' => $users,
            'title' => 'Список из 100 пользователей'
        ]);
    }
}
