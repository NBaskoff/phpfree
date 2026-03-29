<?php

namespace Actions\User;

use Repositories\UserRepository;
use Models\UserModel;

/**
 * Экшен для получения списка всех пользователей системы
 */
class ListUsersAction
{
    /**
     * @param UserRepository $userRepo Репозиторий внедряется автоматически через Contract
     */
    public function __construct(
        private UserRepository $userRepo
    ) {}

    /**
     * Выполняет логику получения данных
     *
     * @return UserModel[]
     */
    public function execute(): array
    {
        return $this->userRepo->all();
    }
}
