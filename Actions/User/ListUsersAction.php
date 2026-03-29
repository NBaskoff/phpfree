<?php

namespace Actions\User;

use Core\Contract;
use Contracts\DatabaseContract;
use Repositories\UserRepository;
use Models\UserModel;

/**
 * Экшен для получения списка всех пользователей
 */
class ListUsersAction
{
    private UserRepository $userRepo;

    public function __construct()
    {
        // Используем Contract только для получения реализации интерфейса БД
        $db = Contract::make(DatabaseContract::class);

        // Вручную создаем репозиторий внутри экшена
        $this->userRepo = new UserRepository($db);
    }

    /**
     * Логика получения данных
     *
     * @return UserModel[]
     */
    public function execute(): array
    {
        return $this->userRepo->all();
    }
}
