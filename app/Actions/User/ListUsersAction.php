<?php

namespace Actions\User;

use Core\Contract;
use Contracts\DatabaseContract;
use Repositories\UserRepository;

/**
 * Экшен получения списка пользователей с их ролями
 */
class ListUsersAction
{
    /**
     * Подготавливает данные для шаблона
     *
     * @return array
     */
    public function __invoke(): array
    {
        $userRepository = new UserRepository();
        $users = $userRepository->all();
        $userRepository->getUsersRoles($users);

        return [
            'users' => $users,
            'title' => 'Список пользователей и их роли'
        ];
    }
}
