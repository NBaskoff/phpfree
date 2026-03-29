<?php

namespace Controllers;

use Actions\User\ListUsersAction;

class UserController extends BaseController
{
    /**
     * Экшен внедряется автоматически через аргументы метода
     */
    public function actionIndexGet(ListUsersAction $action)
    {
        $users = $action->execute();

        return $this->display('user/index', [
            'users' => $users,
            'title' => 'Список из 100 пользователей'
        ]);
    }
}
