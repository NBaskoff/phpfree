<?php

namespace Controllers;

use Actions\User\ListUsersAction;

/**
 * Контроллер управления пользователями
 */
class UserController extends BaseController
{
    /**
     * Отображает страницу со списком всех пользователей
     */
    public function actionIndexGet()
    {
        // Просто создаем экшен напрямую через new
        $action = new ListUsersAction();

        // Получаем данные и выводим
        return $this->display('user/index', [
            'users' => $action->execute(),
            'title' => 'Список пользователей'
        ]);
    }
}
