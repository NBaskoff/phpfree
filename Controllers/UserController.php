<?php

namespace Controllers;

use Core\Contract;
use Actions\User\ListUsersAction;

/**
 * Контроллер управления пользователями
 */
class UserController extends BaseController
{
    /**
     * Отображает страницу со списком всех пользователей
     *
     * @return string|void
     */
    public function actionIndexGet()
    {
        // Создаем экшен через контейнер (чтобы сработал Auto-wiring для конструктора)
        $action = Contract::make(ListUsersAction::class);

        // Получаем список моделей UserModel
        $users = $action->execute();

        // Рендерим шаблон assets/templates/user/index.php
        return $this->display('user/index', [
            'users' => $users,
            'title' => 'Список пользователей'
        ]);
    }
}
