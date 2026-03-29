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
    private UserRepository $userRepo;

    public function __construct()
    {
        // Получаем базу через контракт и создаем репозиторий вручную
        $db = Contract::make(DatabaseContract::class);
        $this->userRepo = new UserRepository($db);
    }

    /**
     * Подготавливает данные для шаблона
     *
     * @return array
     */
    public function __invoke(): array
    {
        $users = $this->userRepo->all();

        // Подгружаем роли для каждого пользователя
        foreach ($users as $user) {
            $user->roles = $this->userRepo->getUserRoles($user->id);
        }

        return [
            'users' => $users,
            'title' => 'Список пользователей и их роли'
        ];
    }
}
