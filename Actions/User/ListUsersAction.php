<?php

namespace Actions\User;

use Repositories\UserRepository;
use Models\UserModel;

/**
 * Экшен для получения списка всех пользователей системы
 */
class ListUsersAction
{
    public function __construct(
        private UserRepository $userRepo
    ) {}

    public function execute(): array
    {
        return $this->userRepo->all();
    }
}
